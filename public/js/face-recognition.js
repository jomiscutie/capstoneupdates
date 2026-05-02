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
        this.headMotionScore = 0;
        this.lastHeadDirection = null;
        this.lastHeadMovementAt = 0;
        this.faceStableTime = 0;
        this.lastFacePosition = null;
        this.faceDetectedCount = 0;
        this.lastDescriptorCache = null;
        this.lastDrawSize = { width: 0, height: 0 };
        this.pendingDetectionPromise = null;
        this.cameraStorageKey = 'norsuPreferredCameraDeviceId';
        this.frameSkipCounter = 0;
        this.skipRate = 1;
        this.blinkState = 'open';
        this.blinkStartTime = 0;
        this.lastEAR = 1.0;
        this.earHistory = [];
        this.debugMode = false;
    }

    async loadModels() {
        if (this.modelsLoaded) return true;

        try {
            const MODEL_URL = (typeof window !== 'undefined' && window.FACE_API_MODEL_BASE) ? window.FACE_API_MODEL_BASE : '/vendor/face-api/model';

            // Load models needed for detection + descriptor
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

    async initializeCamera(videoElement, canvasElement, options = {}) {
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

        // CPU-first camera profiles (no GPU systems benefit from lower live processing load).
        const cameraProfile = (options.cameraProfile || 'speed').toLowerCase();
        const requestedDeviceId = typeof options.cameraDeviceId === 'string' ? options.cameraDeviceId.trim() : '';
        const rememberedDeviceId = requestedDeviceId || this.getRememberedCameraDeviceId();

        // For high-res cameras (1080p), downscale for processing but keep quality for capture
        const isHighResCamera = options.forceDownscale || false;
        this.processingScale = isHighResCamera ? 0.33 : 1.0;

        const speedConstraints = [
            {
                video: {
                    width: { ideal: 640, max: 640 },
                    height: { ideal: 480, max: 480 },
                    frameRate: { ideal: 30, max: 30 },
                    facingMode: 'user'
                }
            },
            {
                video: {
                    width: { ideal: 640, max: 1280 },
                    height: { ideal: 480, max: 720 },
                    frameRate: { ideal: 24, max: 30 },
                    facingMode: { ideal: 'user' }
                }
            },
            {
                video: {
                    width: { ideal: 480, max: 640 },
                    height: { ideal: 360, max: 480 },
                    frameRate: { ideal: 20, max: 24 },
                    facingMode: { ideal: 'user' }
                }
            },
            {
                video: {
                    width: { ideal: 320, max: 480 },
                    height: { ideal: 240, max: 360 },
                    frameRate: { ideal: 15, max: 20 },
                    facingMode: { ideal: 'user' }
                }
            },
            { video: true }
        ];
        const qualityConstraints = [
            {
                video: {
                    width: { ideal: 1920, max: 1920 },
                    height: { ideal: 1080, max: 1080 },
                    frameRate: { ideal: 30, max: 30 },
                    facingMode: 'user'
                }
            },
            {
                video: {
                    width: { ideal: 1280, max: 1920 },
                    height: { ideal: 720, max: 1080 },
                    frameRate: { ideal: 24, max: 30 },
                    facingMode: 'user'
                }
            },
            ...speedConstraints
        ];
        const profileConstraints = cameraProfile === 'quality' ? qualityConstraints : speedConstraints;
        const preferredDeviceConstraints = this.buildPreferredDeviceConstraints(cameraProfile, rememberedDeviceId);
        const constraintsToTry = [...preferredDeviceConstraints, ...profileConstraints];

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

            this.optimizeVideoTrack(stream, cameraProfile);
            this.rememberCameraFromStream(stream);
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

    optimizeVideoTrack(stream, cameraProfile = 'speed') {
        try {
            if (!stream || typeof stream.getVideoTracks !== 'function') return;
            const track = stream.getVideoTracks()[0];
            if (!track || typeof track.getCapabilities !== 'function' || typeof track.applyConstraints !== 'function') {
                return;
            }

            const capabilities = track.getCapabilities() || {};
            const advanced = {};

            // Prefer continuous auto controls for unstable/low-light environments.
            if (Array.isArray(capabilities.focusMode) && capabilities.focusMode.includes('continuous')) {
                advanced.focusMode = 'continuous';
            }
            if (Array.isArray(capabilities.exposureMode) && capabilities.exposureMode.includes('continuous')) {
                advanced.exposureMode = 'continuous';
            }
            if (Array.isArray(capabilities.whiteBalanceMode) && capabilities.whiteBalanceMode.includes('continuous')) {
                advanced.whiteBalanceMode = 'continuous';
            }

            // Avoid forcing brightness/contrast/sharpness values in low-light scenes,
            // because manual boosts can amplify sensor noise on some webcams.

            if (Object.keys(advanced).length > 0) {
                track.applyConstraints({ advanced: [advanced] }).catch(() => {});
            }
        } catch (error) {
            // Best-effort only; unsupported controls should not block camera startup.
            console.debug('Video track optimization skipped:', error);
        }
    }

    buildPreferredDeviceConstraints(cameraProfile, deviceId) {
        if (!deviceId) return [];

        const isQuality = cameraProfile === 'quality';
        const preferred = isQuality
            ? [
                { width: { ideal: 1920, max: 1920 }, height: { ideal: 1080, max: 1080 }, frameRate: { ideal: 30, max: 30 } },
                { width: { ideal: 1280, max: 1920 }, height: { ideal: 720, max: 1080 }, frameRate: { ideal: 24, max: 30 } },
                { width: { ideal: 640, max: 1280 }, height: { ideal: 480, max: 720 }, frameRate: { ideal: 20, max: 30 } }
            ]
            : [
                { width: { ideal: 640, max: 640 }, height: { ideal: 480, max: 480 }, frameRate: { ideal: 24, max: 30 } },
                { width: { ideal: 480, max: 640 }, height: { ideal: 360, max: 480 }, frameRate: { ideal: 20, max: 24 } },
                { width: { ideal: 320, max: 480 }, height: { ideal: 240, max: 360 }, frameRate: { ideal: 15, max: 20 } }
            ];

        return preferred.map((video) => ({
            video: {
                ...video,
                deviceId: { exact: deviceId }
            }
        }));
    }

    rememberCameraFromStream(stream) {
        try {
            const track = stream && typeof stream.getVideoTracks === 'function'
                ? stream.getVideoTracks()[0]
                : null;
            const settings = track && typeof track.getSettings === 'function'
                ? track.getSettings()
                : null;
            const deviceId = settings && typeof settings.deviceId === 'string'
                ? settings.deviceId.trim()
                : '';
            if (deviceId) {
                this.rememberCameraDeviceId(deviceId);
            }
        } catch (error) {
            // Ignore storage/camera metadata failures.
        }
    }

    rememberCameraDeviceId(deviceId) {
        try {
            if (typeof window === 'undefined' || !window.localStorage) return;
            window.localStorage.setItem(this.cameraStorageKey, deviceId);
        } catch (error) {
            // Ignore localStorage failures.
        }
    }

    getRememberedCameraDeviceId() {
        try {
            if (typeof window === 'undefined' || !window.localStorage) return '';
            const stored = window.localStorage.getItem(this.cameraStorageKey);
            return typeof stored === 'string' ? stored.trim() : '';
        } catch (error) {
            return '';
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

        // Always process frames - no skipping for liveness detection
        this.frameSkipCounter++;

        try {
            // Always draw landmarks during liveness detection for visual feedback
            const drawLandmarks = true;
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

            // Draw face detection box (always show for user feedback)
            this.drawDetection(detection, true);

            // Check for liveness (blink detection) - only if we have landmarks
            if (detection.landmarks) {
                this.detectBlink(detection);
                this.trackHeadMovement(detection);
            }

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

        // Calculate eye aspect ratio
        const leftEAR = this.calculateEAR(leftEye);
        const rightEAR = this.calculateEAR(rightEye);
        const avgEAR = (leftEAR + rightEAR) / 2;

        // Fixed threshold: 0.25 works for most cameras
        // Eyes open: ~0.30, Eyes closed: ~0.15
        const BLINK_THRESHOLD = 0.23;
        const now = Date.now();
        const isBlinking = avgEAR < BLINK_THRESHOLD;

        // Debug logging every 500ms
        if (this.debugMode && now % 500 < 100) {
            console.log(`EAR: ${avgEAR.toFixed(3)}, threshold: ${BLINK_THRESHOLD}, blinking: ${isBlinking}, state: ${this.blinkState}, blinks: ${this.blinkCount}`);
        }

        // Simple state machine: open -> closing -> open
        if (isBlinking && this.blinkState === 'open') {
            this.blinkState = 'closing';
            this.blinkStartTime = now;
            if (this.debugMode) console.log('Eyes closing...');
        } else if (!isBlinking && this.blinkState === 'closing') {
            const blinkDuration = now - this.blinkStartTime;
            if (blinkDuration >= 80 && blinkDuration <= 600) {
                // Valid blink detected
                this.blinkCount++;
                this.lastBlinkTime = now;
                if (this.debugMode) console.log('BLINK DETECTED! Total:', this.blinkCount, 'Duration:', blinkDuration + 'ms');
            }
            this.blinkState = 'open';
        } else if (!isBlinking) {
            this.blinkState = 'open';
        }
    }

    trackHeadMovement(detection) {
        if (!detection || !detection.detection || !detection.detection.box) return;
        const box = detection.detection.box;
        if (!box.width || !box.height) return;

        const now = Date.now();
        const center = {
            x: box.x + (box.width / 2),
            y: box.y + (box.height / 2),
            width: box.width,
            height: box.height,
            at: now
        };

        this.headMovements.push(center);
        if (this.headMovements.length > 12) {
            this.headMovements.shift();
        }
        if (this.headMovements.length < 2) {
            return;
        }

        const prev = this.headMovements[this.headMovements.length - 2];
        const dxNorm = (center.x - prev.x) / Math.max(1, center.width);
        const dyNorm = (center.y - prev.y) / Math.max(1, center.height);
        const moveThreshold = 0.03;

        if (Math.abs(dxNorm) < moveThreshold && Math.abs(dyNorm) < moveThreshold) {
            return;
        }

        const direction = Math.abs(dxNorm) >= Math.abs(dyNorm)
            ? (dxNorm > 0 ? 'right' : 'left')
            : (dyNorm > 0 ? 'down' : 'up');

        if (this.lastHeadDirection && this.lastHeadDirection !== direction) {
            if (now - this.lastHeadMovementAt > 180) {
                this.headMotionScore = Math.min(6, this.headMotionScore + 1);
                this.lastHeadMovementAt = now;
            }
        } else if (!this.lastHeadDirection) {
            this.lastHeadMovementAt = now;
        }

        this.lastHeadDirection = direction;
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
                // Slightly shorter stable window to reduce false liveness failures on kiosk.
                return (now - this.faceStableTime) >= 1200;
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
        const minFaceCoverage = Number.isFinite(Number(config.minFaceCoverage))
            ? Number(config.minFaceCoverage)
            : 0.06;
        const minDetectionScore = Number.isFinite(Number(config.minDetectionScore))
            ? Number(config.minDetectionScore)
            : 0.5;
        const minAcceptedSamples = Math.max(1, Number(config.minAcceptedSamples || Math.min(2, sampleCount)));

        if (cacheMs > 0) {
            const cached = this.getCachedDescriptor(cacheMs);
            if (cached) {
                return JSON.stringify(cached);
            }
        }

        // Capture multiple samples for stable encoding (reduces false positives)
        this.faceDescriptors = [];

        const maxAttempts = Math.max(sampleCount + 2, sampleCount + 4);
        for (let i = 0; i < maxAttempts && this.faceDescriptors.length < sampleCount; i++) {
            const detection = await this.detectFace(true, { drawLandmarks, detectorProfile });
            if (detection && detection.descriptor && this.isDetectionQualityAcceptable(detection, {
                minFaceCoverage,
                minDetectionScore
            })) {
                this.faceDescriptors.push(Array.from(detection.descriptor));
            }
            if (i < maxAttempts - 1) {
                await new Promise(resolve => setTimeout(resolve, intervalMs));
            }
        }

        if (this.faceDescriptors.length < minAcceptedSamples) {
            throw new Error('Face quality is too low. Move closer to the camera and improve lighting, then try again.');
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

    isDetectionQualityAcceptable(detection, options = {}) {
        if (!detection || !detection.detection || !detection.detection.box) return false;
        if (!this.video || !this.video.videoWidth || !this.video.videoHeight) return true;

        const minFaceCoverage = Number(options.minFaceCoverage || 0.06);
        const minDetectionScore = Number(options.minDetectionScore || 0.5);
        const box = detection.detection.box;
        const faceArea = Math.max(0, box.width) * Math.max(0, box.height);
        const frameArea = Math.max(1, this.video.videoWidth * this.video.videoHeight);
        const coverage = faceArea / frameArea;
        const score = Number.isFinite(Number(detection.detection.score)) ? Number(detection.detection.score) : 1;

        return coverage >= minFaceCoverage && score >= minDetectionScore;
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
        this.headMotionScore = 0;
        this.lastHeadDirection = null;
        this.lastHeadMovementAt = 0;
        this.faceStableTime = 0;
        this.lastFacePosition = null;
        this.faceDetectedCount = 0;
        this.frameSkipCounter = 0;
        this.blinkState = 'open';
        this.blinkStartTime = 0;
        this.lastEAR = 1.0;
        this.earHistory = [];
    }

    checkLiveness(detection = null, options = {}) {
        const minBlinks = options.minBlinks || 1;
        const hasBlink = this.blinkCount >= minBlinks;
        const strongBlink = this.blinkCount >= 2;
        const isStable = detection ? this.checkFaceStability(detection) : false;
        const hasHeadMotion = this.headMotionScore >= 2;

        if (detection) {
            this.faceDetectedCount++;
        }

        const strictLiveness = strongBlink && (isStable || hasHeadMotion);
        const motionBlinkLiveness = hasBlink && hasHeadMotion;
        const fastLiveness = hasBlink && isStable;

        return strictLiveness || motionBlinkLiveness || fastLiveness || (hasBlink && this.faceDetectedCount >= 3);
    }
}

// Global instance
const faceRecognition = new FaceRecognition();



