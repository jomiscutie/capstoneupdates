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
    }

    async loadModels() {
        if (this.modelsLoaded) return true;

        try {
            const MODEL_URL = (typeof window !== 'undefined' && window.FACE_API_MODEL_BASE) ? window.FACE_API_MODEL_BASE : '/vendor/face-api/model';

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
                faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL)
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

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: 640,
                    height: 480,
                    facingMode: 'user'
                }
            });
            this.video.srcObject = stream;
            return true;
        } catch (error) {
            console.error('Error accessing camera:', error);
            return false;
        }
    }

    async detectFace() {
        if (!this.modelsLoaded || !this.video || this.video.readyState !== 4) {
            return null;
        }

        try {
            const detection = await faceapi
                .detectSingleFace(this.video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor()
                .withFaceExpressions();

            if (detection) {
                // Draw face detection box
                this.drawDetection(detection);
                
                // Check for liveness (blink detection)
                this.detectBlink(detection);
                
                return detection;
            }
            return null;
        } catch (error) {
            console.error('Error detecting face:', error);
            return null;
        }
    }

    drawDetection(detection) {
        if (!this.canvas || !this.ctx) return;

        const displaySize = {
            width: this.video.videoWidth,
            height: this.video.videoHeight
        };

        faceapi.matchDimensions(this.canvas, displaySize);

        const resizedDetection = faceapi.resizeResults(detection, displaySize);
        
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        faceapi.draw.drawDetections(this.canvas, resizedDetection);
        faceapi.draw.drawFaceLandmarks(this.canvas, resizedDetection);
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

    async captureFaceEncoding() {
        // Optimized: Capture fewer samples but faster for better UX
        this.faceDescriptors = [];
        const captureCount = 3; // Reduced from 5 to 3 for faster capture
        const captureInterval = 300; // Reduced from 500ms to 300ms

        for (let i = 0; i < captureCount; i++) {
            const detection = await this.detectFace();
            if (detection && detection.descriptor) {
                this.faceDescriptors.push(Array.from(detection.descriptor));
            }
            if (i < captureCount - 1) {
                await new Promise(resolve => setTimeout(resolve, captureInterval));
            }
        }

        if (this.faceDescriptors.length === 0) {
            throw new Error('No face detected. Please ensure your face is clearly visible.');
        }

        // Average the descriptors for better accuracy
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
        // Enhanced verification: Multiple attempts for better accuracy
        const stored = JSON.parse(storedEncoding);
        const attempts = 5; // Number of verification attempts
        const distances = [];
        const encodings = [];

        // Capture multiple face samples
        for (let i = 0; i < attempts; i++) {
            const detection = await this.detectFace();
            if (detection && detection.descriptor) {
                const current = Array.from(detection.descriptor);
                const distance = this.calculateDistance(stored, current);
                distances.push(distance);
                encodings.push(current);
            }
            // Small delay between attempts
            if (i < attempts - 1) {
                await new Promise(resolve => setTimeout(resolve, 200));
            }
        }

        if (distances.length === 0) {
            return { verified: false, message: 'No face detected', confidence: 0 };
        }

        // Calculate average distance (more reliable than single measurement)
        const avgDistance = distances.reduce((a, b) => a + b, 0) / distances.length;
        
        // Calculate min distance (best match)
        const minDistance = Math.min(...distances);
        
        // Stricter threshold: 0.4 (lower = stricter, prevents false matches)
        const threshold = 0.4;
        
        // Confidence score: 0-100 (lower distance = higher confidence)
        const confidence = Math.max(0, Math.min(100, (1 - (avgDistance / threshold)) * 100));
        
        // Require at least 3 successful matches out of 5 attempts
        const successfulMatches = distances.filter(d => d < threshold).length;
        const matchRatio = successfulMatches / distances.length;
        
        // Verification passes if:
        // 1. Average distance is below threshold
        // 2. At least 60% of attempts match (3 out of 5)
        // 3. Minimum distance is reasonable
        const verified = avgDistance < threshold && matchRatio >= 0.6 && minDistance < threshold * 1.2;
        
        // Average the encodings for final submission
        const avgEncoding = this.averageDescriptors(encodings);

        return {
            verified: verified,
            distance: avgDistance,
            minDistance: minDistance,
            confidence: Math.round(confidence),
            matchRatio: matchRatio,
            attempts: distances.length,
            encoding: JSON.stringify(avgEncoding),
            message: verified 
                ? `Face verified with ${Math.round(confidence)}% confidence` 
                : `Face verification failed. Distance: ${avgDistance.toFixed(2)} (threshold: ${threshold})`
        };
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
        // Enhanced liveness check: Stricter requirements to prevent spoofing
        // 1. Require at least 2 blinks (more reliable than 1)
        // 2. Face must be stable for 2+ seconds (prevents photo spoofing)
        // 3. Face must be detected multiple times (prevents static images)
        
        const hasBlink = this.blinkCount >= 2; // Increased from 1 to 2
        const isStable = detection ? this.checkFaceStability(detection) : false;
        
        // If face is detected multiple times, increment counter
        if (detection) {
            this.faceDetectedCount++;
        }
        
        // Face detected at least 10 times (about 3 seconds) for better reliability
        const hasMultipleDetections = this.faceDetectedCount >= 10; // Increased from 5
        
        // Require BOTH blink AND stability for highest security
        // OR multiple detections as fallback
        const strictLiveness = hasBlink && isStable;
        const fallbackLiveness = hasMultipleDetections && (hasBlink || isStable);
        
        return strictLiveness || fallbackLiveness;
    }
}

// Global instance
const faceRecognition = new FaceRecognition();



