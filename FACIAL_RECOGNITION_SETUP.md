# Facial Recognition System Setup Guide

## Overview
This system implements facial recognition for student time in/out with anti-spoofing measures.

## Features
- ✅ Face detection and encoding using face-api.js
- ✅ Liveness detection (blink detection)
- ✅ Multiple face capture for better accuracy
- ✅ Face verification during time in/out
- ✅ Anti-spoofing measures

## Setup Instructions

### 1. Install face-api.js
Add this to your student registration and dashboard pages:

```html
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.6.9/dist/face-api.min.js"></script>
<script src="{{ asset('js/face-recognition.js') }}"></script>
```

### 2. Database
The migration has been run. The `face_encoding` field stores the facial recognition data.

### 3. Implementation Steps

#### For Student Registration:
1. Add face capture modal to registration form
2. Capture face encoding before form submission
3. Include face_encoding in registration request

#### For Time In/Out:
1. Add face verification modal
2. Verify face before submitting time in/out
3. Include face_encoding in the request

## Anti-Spoofing Measures

1. **Liveness Detection**: Requires at least 2 blinks during face capture
2. **Multiple Captures**: Takes 5 face captures and averages them
3. **Real-time Video**: Uses live video stream, not static photos
4. **Face Distance Calculation**: Uses Euclidean distance for verification
5. **Threshold**: Configurable similarity threshold (default: 0.6)

## Security Features

- Face encoding stored as JSON array (128 dimensions)
- Server-side verification
- Cannot use photos (requires live video)
- Blink detection prevents photo spoofing
- Multiple capture attempts for accuracy

## Testing

1. Register a student with face capture
2. Test time in with face verification
3. Test time out with face verification
4. Try to use a photo (should fail)
5. Adjust threshold if needed (lower = stricter)

## Troubleshooting

- **Camera not working**: Check browser permissions
- **Face not detected**: Ensure good lighting, face directly at camera
- **Verification fails**: Adjust threshold in AttendanceController.php
- **Models not loading**: Check CDN connection



