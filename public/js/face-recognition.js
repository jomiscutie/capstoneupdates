// Face Recognition System using face-api.js
// This file handles face detection, encoding, and verification

class FaceRecognition {
    constructor() {
        this.modelsLoaded = false;
        this.video = null;
        this.canvas = null;
        this.ctx = null;
        this.isDetecting = false;
        this.faceDescriptors = [];
        this.blinkCount = 0;
        this.lastBlinkTime = 0;
        this.headMovements = [];
        this.faceStableTime = 0;
        this.lastFacePosition = null;
        this.faceDetectedCount = 0;
        this.lastDescriptorCache = null;
        this.lastDrawSize = { width: 0, height: 0 };
        this.pendingDetectionPromise = null;
    }

    async loadModels() {
        if (this.modelsLoaded) return true;

        try {
            const MODEL_URL = (typeof window !== 'undefined' && window.FACE_API_MODEL_BASE) ? window.FACE_API_MODEL_BASE : '/vendor/face-api/model';

            // Load only models needed for detection + descriptor (skip faceExpressionNet to reduce lag)
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);

            this.modelsLoaded = true;
            console.log('Face API models loaded successfully');
            return true;
        } catch (error) {
            console.error('Error loading face models:', error);
            return false;
        }
    }

    async initializeCamera(videoElement, canvasElement) {
        this.video = videoElement;
        this.canvas = canvasElement;
        this.ctx = canvasElement.getContext('2d');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            return {
                ok: false,
                code: 'UNSUPPORTED',
                message: 'This browser does not support camera access. Use a modern browser like Chrome, Edge, or Firefox.'
            };
        }

        // Multi-step fallback to improve compatibility across devices/browsers.
        const constraintsToTry = [
            {
                video: {
                    width: { ideal: 320, max: 480 },
                    height: { ideal: 240, max: 360 },
                    facingMode: 'user'
                }
            },
            {
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: { ideal: 'user' }
                }
            },
            {
                video: true
            }
        ];

        try {
            let stream = null;
            let lastError = null;

            for (const constraints of constraintsToTry) {
                try {
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    if (stream) break;
                } catch (err) {
                    lastError = err;
                }
            }

            if (!stream) {
                throw lastError || new Error('Unable to access camera');
            }

            this.video.srcObject = stream;
            return { ok: true };
        } catch (error) {
            console.error('Error accessing camera:', error);
            return {
                ok: false,
                code: error && error.name ? error.name : 'CAMERA_ERROR',
                message: this.getCameraErrorMessage(error)
            };
        }
    }

    getCameraErrorMessage(error) {
        const name = error && error.name ? error.name : '';
        if (name === 'NotAllowedError' || name === 'SecurityError') {
            return 'Camera permission was denied. Allow camera access in your browser/site settings, then try again.';
        }
        if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
            return 'No camera device was found. Connect/enable a camera, then retry.';
        }
        if (name === 'NotReadableError' || name === 'TrackStartError') {
            return 'Camera is busy or blocked by another app (Zoom/Meet/Teams/Camera app). Close it, then retry.';
        }
        if (name === 'OverconstrainedError' || name === 'ConstraintNotSatisfiedError') {
            return 'Camera settings are not supported on this device. Retry and the system will use compatible defaults.';
        }
        return 'Camera is unavailable right now. Check permission/device availability and try again.';
    }

    getDetectorOptions(options = {}) {
        const profile = options.detectorProfile || 'normal';
        const customInput = Number(options.inputSize);
        const customThreshold = Number(options.scoreThreshold);
        const presets = {
            fast: { inputSize: 96, scoreThreshold: 0.45 },
            normal: { inputSize: 128, scoreThreshold: 0.5 },
            strict: { inputSize: 160, scoreThreshold: 0.55 }
        };
        const base = presets[profile] || presets.normal;

        const inputSize = Number.isFinite(customInput) && customInput >= 64 && customInput <= 416
            ? customInput
            : base.inputSize;
        const scoreThreshold = Number.isFinite(customThreshold) && customThreshold >= 0.1 && customThreshold <= 0.99
            ? customThreshold
            : base.scoreThreshold;

        return new faceapi.TinyFaceDetectorOptions({ inputSize, scoreThreshold });
    }

    async detectFace(includeDescriptor = true, options = {}) {
        if (!this.modelsLoaded || !this.video || this.video.readyState !== 4) {
            return null;
        }
        if (this.pendingDetectionPromise) {
            return this.pendingDetectionPromise;
        }

        try {
            const drawLandmarks = options.drawLandmarks !== false;
            const detectorOptions = this.getDetectorOptions(options);
            const detectionTask = faceapi
                .detectSingleFace(this.video, detectorOptions)
                .withFaceLandmarks();
            this.pendingDetectionPromise = includeDescriptor
                ? detectionTask.withFaceDescriptor()
                : detectionTask;
            const detection = await this.pendingDetectionPromise;

            if (!detection) {
                return null;
            }

            // Draw face detection box
            this.drawDetection(detection, drawLandmarks);

            // Check for liveness (blink detection)
            this.detectBlink(detection);

            if (includeDescriptor && detection.descriptor) {
                this.lastDescriptorCache = {
                    descriptor: Array.from(detection.descriptor),
                    capturedAt: Date.now()
                };
            }

            return detection;
        } catch (error) {
            console.error('Error detecting face:', error);
            return null;
        } finally {
            this.pendingDetectionPromise = null;
        }
    }

    getCachedDescriptor(maxAgeMs = 1800) {
        if (!this.lastDescriptorCache || !this.lastDescriptorCache.descriptor) return null;
        if ((Date.now() - this.lastDescriptorCache.capturedAt) > maxAgeMs) return null;
        return this.lastDescriptorCache.descriptor;
    }

    drawDetection(detection, drawLandmarks = true) {
        if (!this.canvas || !this.ctx) return;

        const displaySize = {
            width: this.video.videoWidth,
            height: this.video.videoHeight
        };

        if (displaySize.width !== this.lastDrawSize.width || displaySize.height !== this.lastDrawSize.height) {
            faceapi.matchDimensions(this.canvas, displaySize);
            this.lastDrawSize = displaySize;
        }

        const resizedDetection = faceapi.resizeResults(detection, displaySize);
        
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        faceapi.draw.drawDetections(this.canvas, resizedDetection);
        if (drawLandmarks) {
            faceapi.draw.drawFaceLandmarks(this.canvas, resizedDetection);
        }
    }

    detectBlink(detection) {
        if (!detection.landmarks) return;

        const landmarks = detection.landmarks.positions;
        const leftEye = [
            landmarks[36], landmarks[37], landmarks[38],
            landmarks[39], landmarks[40], landmarks[41]
        ];
        const rightEye = [
            landmarks[42], landmarks[43], landmarks[44],
            landmarks[45], landmarks[46], landmarks[47]
        ];

        // Calculate eye aspect ratio (simplified)
        const leftEAR = this.calculateEAR(leftEye);
        const rightEAR = this.calculateEAR(rightEye);
        const avgEAR = (leftEAR + rightEAR) / 2;

        // Blink detection threshold (0.25 is standard, keeping it for accuracy)
        // Lower threshold = more sensitive to blinks
        if (avgEAR < 0.25) {
            const now = Date.now();
            // Debounce time: 300ms to prevent false blink detection
            if (now - this.lastBlinkTime > 300) {
                this.blinkCount++;
                this.lastBlinkTime = now;
            }
        }
    }

    checkFaceStability(detection) {
        if (!detection || !detection.detection) return false;

        const currentPosition = {
            x: detection.detection.box.x,
            y: detection.detection.box.y,
            width: detection.detection.box.width,
            height: detection.detection.box.height
        };

        const now = Date.now();

        if (this.lastFacePosition) {
            // Calculate position difference
            const dx = Math.abs(currentPosition.x - this.lastFacePosition.x);
            const dy = Math.abs(currentPosition.y - this.lastFacePosition.y);
            const dw = Math.abs(currentPosition.width - this.lastFacePosition.width);
            const dh = Math.abs(currentPosition.height - this.lastFacePosition.height);

            // If face position is stable (small movement)
            const threshold = 20; // pixels
            if (dx < threshold && dy < threshold && dw < threshold && dh < threshold) {
                if (this.faceStableTime === 0) {
                    this.faceStableTime = now;
                }
                // Face has been stable for at least 2 seconds (increased for better security)
                return (now - this.faceStableTime) >= 2000;
            } else {
                // Face moved, reset stability timer
                this.faceStableTime = 0;
            }
        }

        this.lastFacePosition = currentPosition;
        return false;
    }

    calculateEAR(eyePoints) {
        // Eye Aspect Ratio calculation
        const vertical1 = Math.abs(eyePoints[1].y - eyePoints[5].y);
        const vertical2 = Math.abs(eyePoints[2].y - eyePoints[4].y);
        const horizontal = Math.abs(eyePoints[0].x - eyePoints[3].x);
        return (vertical1 + vertical2) / (2 * horizontal);
    }

    async captureFaceEncoding(config = {}) {
        const sampleCount = Math.max(1, Number(config.sampleCount || 3));
        const intervalMs = Math.max(70, Number(config.intervalMs || 220));
        const cacheMs = Number(config.useCacheMs || 0);
        const drawLandmarks = config.drawLandmarks !== false;
        const detectorProfile = config.detectorProfile || 'normal';

        if (cacheMs > 0) {
            const cached = this.getCachedDescriptor(cacheMs);
            if (cached) {
                return JSON.stringify(cached);
            }
        }

        // Capture multiple samples for stable encoding (reduces false positives)
        this.faceDescriptors = [];

        const maxAttempts = Math.max(sampleCount, sampleCount + 3);
        for (let i = 0; i < maxAttempts && this.faceDescriptors.length < sampleCount; i++) {
            const detection = await this.detectFace(true, { drawLandmarks, detectorProfile });
            if (detection && detection.descriptor) {
                this.faceDescriptors.push(Array.from(detection.descriptor));
            }
            if (i < maxAttempts - 1) {
                await new Promise(resolve => setTimeout(resolve, intervalMs));
            }
        }

        if (this.faceDescriptors.length < 2) {
            throw new Error('No face detected. Please ensure your face is clearly visible and well lit.');
        }

        // Average the descriptors for better accuracy and fewer false matches
        const avgDescriptor = this.averageDescriptors(this.faceDescriptors);
        return JSON.stringify(avgDescriptor);
    }

    averageDescriptors(descriptors) {
        const avg = new Array(128).fill(0);
        descriptors.forEach(desc => {
            desc.forEach((val, idx) => {
                avg[idx] += val;
            });
        });
        return avg.map(val => val / descriptors.length);
    }

    async verifyFace(storedEncoding) {
        try {
            let stored = storedEncoding;
            if (typeof stored === 'string') {
                try { stored = JSON.parse(stored); } catch (e) { stored = JSON.parse(stored.replace(/^"|"$/g, '')); }
            }
            if (!Array.isArray(stored) || stored.length !== 128) {
                return { verified: false, message: 'Invalid stored face data', confidence: 0, distance: 1, matchRatio: 0, attempts: 0, encoding: null };
            }

            let current = this.getCachedDescriptor(1800);
            if (!current) {
                const detection = await this.detectFace(true, { drawLandmarks: false, detectorProfile: 'fast' });
                if (!detection || !detection.descriptor) {
                    return { verified: false, message: 'No face detected', confidence: 0, distance: 1, matchRatio: 0, attempts: 0, encoding: null };
                }
                current = Array.from(detection.descriptor);
            }
            const rawT = (typeof window !== 'undefined' && window.FACE_SAME_PERSON_THRESHOLD != null)
                ? Number(window.FACE_SAME_PERSON_THRESHOLD)
                : 0.5;
            const threshold = Number.isFinite(rawT) && rawT > 0.05 && rawT < 1.5 ? rawT : 0.5;
            let distance = this.calculateDistance(stored, current);
            let attempts = 1;

            // Borderline distances get a stricter second pass to preserve accuracy without slowing all checks.
            const borderlineLow = threshold * 0.88;
            const borderlineHigh = threshold * 1.12;
            if (distance > borderlineLow && distance < borderlineHigh) {
                const confirmDetection = await this.detectFace(true, {
                    drawLandmarks: false,
                    detectorProfile: 'strict',
                    inputSize: 160,
                    scoreThreshold: 0.55
                });
                if (confirmDetection && confirmDetection.descriptor) {
                    attempts = 2;
                    const confirmDescriptor = Array.from(confirmDetection.descriptor);
                    const mergedCurrent = this.averageDescriptors([current, confirmDescriptor]);
                    const confirmDistance = this.calculateDistance(stored, confirmDescriptor);
                    distance = Math.min(
                        this.calculateDistance(stored, mergedCurrent),
                        confirmDistance
                    );
                    current = mergedCurrent;
                }
            }
            const confidence = Math.max(0, Math.min(100, (1 - (distance / threshold)) * 100));
            const verified = distance <= threshold;

            return {
                verified: verified,
                distance: distance,
                minDistance: distance,
                confidence: Math.round(confidence),
                matchRatio: verified ? 1 : 0,
                attempts: attempts,
                encoding: JSON.stringify(current),
                message: verified ? 'Face verified' : 'Face verification failed'
            };
        } catch (err) {
            console.error('verifyFace error:', err);
            return { verified: false, message: err.message || 'Verification error', confidence: 0, distance: 1, matchRatio: 0, attempts: 0, encoding: null };
        }
    }

    calculateDistance(encoding1, encoding2) {
        let sum = 0;
        for (let i = 0; i < encoding1.length; i++) {
            const diff = encoding1[i] - encoding2[i];
            sum += diff * diff;
        }
        return Math.sqrt(sum);
    }

    stopCamera() {
        if (this.video && this.video.srcObject) {
            const tracks = this.video.srcObject.getTracks();
            tracks.forEach(track => track.stop());
            this.video.srcObject = null;
        }
        if (this.canvas && this.ctx) {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        }
        this.lastDescriptorCache = null;
        this.lastDrawSize = { width: 0, height: 0 };
        this.pendingDetectionPromise = null;
    }

    resetLiveness() {
        this.blinkCount = 0;
        this.lastBlinkTime = 0;
        this.headMovements = [];
        this.faceStableTime = 0;
        this.lastFacePosition = null;
        this.faceDetectedCount = 0;
    }

    checkLiveness(detection = null) {
        const hasBlink = this.blinkCount >= 2;
        const isStable = detection ? this.checkFaceStability(detection) : false;

        if (detection) {
            this.faceDetectedCount++;
        }

        // Strict liveness: requires both blink AND stability
        const strictLiveness = hasBlink && isStable;
        
        // Moderate liveness: requires blink OR stability plus more detections
        const hasEnoughDetections = this.faceDetectedCount >= 8;
        const moderateLiveness = hasEnoughDetections && (hasBlink || isStable);
        
        // Note: The pure detection count fallback (canProceed) was removed
        // as it was too lenient and could allow photo-based bypass attempts.
        // Server-side verification still runs as an additional layer of security.
        // If users have issues with liveness, they can use password verification as fallback.

        return strictLiveness || moderateLiveness;
    }
}

// Global instance
const faceRecognition = new FaceRecognition();



