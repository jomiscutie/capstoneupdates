# Face Verification System - Troubleshooting Guide

This comprehensive guide covers all aspects of troubleshooting the face verification and camera activation issues in the NORSU OJT DTR application.

## Table of Contents
1. [Browser Permission Issues](#1-browser-permission-issues)
2. [Camera Hardware Access](#2-camera-hardware-access)
3. [Application Configuration](#3-application-configuration)
4. [Network and CDN Issues](#4-network-and-cdn-issues)
5. [Device-Specific Solutions](#5-device-specific-solutions)
6. [Performance Optimization](#6-performance-optimization)
7. [Error Codes and Messages](#7-error-codes-and-messages)
8. [Preventive Measures](#8-preventive-measures)

---

## 1. Browser Permission Issues

### Symptoms
- Camera does not activate
- No video stream displayed
- "Camera access denied" error
- Modal opens but shows black screen

### Solutions

#### Chrome/Edge (Chromium-based)
1. **Check Site Permissions**
   - Click the lock icon 🔒 in the address bar
   - Go to "Site settings"
   - Ensure "Camera" is set to "Allow"

2. **Reset Permissions**
   - Navigate to `chrome://settings/content/camera`
   - Find the application URL
   - Click "Reset permissions"

3. **Allow Camera in Incognito**
   - Go to `chrome://flags/#unsafely-treat-insecure-origin-as-secure`
   - Enable the flag
   - Add your site URL as the value
   - Relaunch Chrome

#### Firefox
1. **Check Permissions**
   - Click the shield icon 🔖 in the address bar
   - Check "Camera" permissions

2. **Reset Permissions**
   - Go to `about:permissions`
   - Search for your site
   - Click "Reset All Permissions"

#### Safari (macOS/iOS)
1. **Enable Camera Access**
   - Go to Safari → Preferences → Websites → Camera
   - Select your site
   - Choose "Allow"

2. **System Settings**
   - Go to System Settings → Privacy & Security → Camera
   - Enable Safari access

#### Safari (iOS)
1. Go to Settings → Safari → Camera
2. Select "Allow"

### HTTP vs HTTPS
**Important**: Camera access requires **HTTPS** or **localhost**.

- `http://` → Camera will NOT work (except on localhost)
- `https://` → Camera works
- `localhost` → Camera works

If deploying to production, ensure SSL certificate is valid.

---

## 2. Camera Hardware Access

### Symptoms
- Camera indicator light doesn't turn on
- "Device not found" error
- "Could not start video source" error
- Camera works in other apps but not this one

### Solutions

#### Windows
1. **Check Device Manager**
   - Press `Win + X` → Device Manager
   - Expand "Cameras" or "Imaging devices"
   - Ensure no yellow exclamation marks

2. **Update Drivers**
   - Right-click camera device
   - Select "Update driver"
   - Search automatically

3. **Privacy Settings**
   - Go to Settings → Privacy & Security → Camera
   - Enable "Camera access"
   - Enable "Let apps use camera"

4. **Disable Hardware Acceleration**
   - Some cameras conflict with browser hardware acceleration
   - Try disabling it in browser settings

#### macOS
1. **Check System Preferences**
   - Go to System Settings → Camera
   - Ensure no apps are blocking access

2. **Terminal Commands**
   ```bash
   # Restart camera service
   sudo killall VDCAssistant
   ```

3. **Check Energy Saver**
   - Go to System Settings → Battery
   - Ensure "Prevent automatic sleeping when camera is in use" is enabled

#### Linux
1. **Check Video Device**
   ```bash
   # List video devices
   ls /dev/video*
   
   # Check permissions
   ls -la /dev/video0
   ```

2. **Add User to Video Group**
   ```bash
   sudo usermod -aG video $USER
   ```

3. **Install V4L Utils**
   ```bash
   sudo apt install v4l-utils
   v4l2-ctl --list-devices
   ```

#### Mobile Devices (iOS/Android)
1. **iOS**
   - Go to Settings → Privacy → Camera
   - Enable browser (Safari) access

2. **Android**
   - Go to Settings → Apps → Browser → Permissions
   - Enable Camera
   - Go to Settings → Apps → Browser → Default Apps → Opening Links
   - Ensure "Include supported links"

### Multiple Cameras
If you have multiple cameras:
- External webcam vs. built-in camera
- The system uses `facingMode: 'user'` (front camera)
- Ensure the correct camera is selected

---

## 3. Application Configuration

### Check JavaScript Console for Errors

Open Developer Tools (F12) and check for:
- `getUserMedia` errors
- Face-api.js loading failures
- CDN access issues

### Common Console Errors

#### "NotAllowedError: Permission denied"
- Camera permission blocked
- See [Browser Permission Issues](#1-browser-permission-issues)

#### "NotFoundError: No camera found"
- No camera detected
- See [Camera Hardware Access](#2-camera-hardware-access)

#### "NotReadableError: Could not start video source"
- Camera in use by another application
- Close other apps using camera
- Restart browser

#### "OverconstrainedError: Constraints could not be satisfied"
- Camera doesn't support requested resolution
- Reduce resolution in `face-recognition.js`

### Modify Face Recognition Settings

File: `public/js/face-recognition.js`

```javascript
// Line 48-54: Adjust camera constraints
async initializeCamera(videoElement, canvasElement) {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { min: 320, ideal: 640, max: 1280 },  // Adjust resolution
                height: { min: 240, ideal: 480, max: 720 },
                facingMode: 'user',
                frameRate: { ideal: 30, max: 30 }  // Reduce if slow
            }
        });
        // ...
    }
}
```

### CDN Configuration

The system uses CDN for face-api.js models:
```javascript
// Line 24: Model URL
const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.6.9/model';
```

**Alternative CDNs** (if primary fails):
- `https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.6.9/model/`
- `https://unpkg.com/@vladmandic/face-api@1.6.9/model/`

---

## 4. Network and CDN Issues

### Symptoms
- "Error loading face models"
- Models load slowly or fail
- Face detection doesn't start

### Solutions

#### 1. Check Network Connectivity
- Ensure internet connection is active
- CDN must be accessible

#### 2. Test CDN Access
Directly visit in browser:
```
https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.6.9/model/tiny_face_detector_model-weights_manifest.json
```

Should return JSON file.

#### 3. Fallback Mechanism
Add fallback CDN in `face-recognition.js`:
```javascript
async loadModels() {
    if (this.modelsLoaded) return true;
    
    const cdnUrls = [
        'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.6.9/model',
        'https://unpkg.com/@vladmandic/face-api@1.6.9/model'
    ];
    
    for (const url of cdnUrls) {
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(url),
                faceapi.nets.faceLandmark68Net.loadFromUri(url),
                faceapi.nets.faceRecognitionNet.loadFromUri(url),
                faceapi.nets.faceExpressionNet.loadFromUri(url)
            ]);
            this.modelsLoaded = true;
            console.log('Face API models loaded successfully');
            return true;
        } catch (error) {
            console.warn(`Failed to load from ${url}, trying next...`);
        }
    }
    return false;
}
```

#### 4. Offline Models (Advanced)
Download models locally:
```bash
# Download to public/models/
wget https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.6.9/model/*
```

Update configuration:
```javascript
const MODEL_URL = '/models';  // Local path
```

---

## 5. Device-Specific Solutions

### Desktop Computers
- Use external USB webcam if built-in fails
- Ensure adequate lighting (300+ lux recommended)
- Position face 1-2 feet from camera

### Laptop Computers
- Clean camera lens
- Close other applications
- Disable power-saving mode

### Mobile Devices
1. **iPhone/iPad**
   - Use Safari (Chrome may have issues)
   - Enable motion & orientation access
   - Ensure portrait orientation

2. **Android**
   - Use Chrome
   - Enable "Fullscreen" mode
   - Check for "Do Not Disturb" interference

### Tablets
- Ensure landscape orientation
- Hold device at eye level
- Avoid reflective backgrounds

### Virtual Machines
Camera access is **not supported** in most VMs:
- Use physical device instead
- Set up USB passthrough (VMware/Hyper-V)

---

## 6. Performance Optimization

### Slow Performance
- Reduce video resolution
- Close unnecessary browser tabs
- Disable browser extensions

### How to Optimize

#### Adjust Detection Interval
In `face-recognition.js`:
```javascript
// Increase capture interval for slower devices
const captureInterval = 500;  // Increase from 300
```

#### Reduce Verification Attempts
```javascript
// Line 221: Reduce attempts
const attempts = 3;  // Reduce from 5
```

#### Simplify Liveness Detection
```javascript
// Line 318: Reduce blink requirement
const hasBlink = this.blinkCount >= 1;  // Reduce from 2
```

### Browser Performance Settings

#### Chrome
- `chrome://settings/performance` → Disable background tabs
- `chrome://flags/` → Disable hardware acceleration (if needed)

#### Firefox
- `about:preferences` → Performance → Disable hardware acceleration

---

## 7. Error Codes and Messages

| Error | Meaning | Solution |
|-------|---------|----------|
| `NotAllowedError` | Permission denied | Allow camera in browser/site settings |
| `NotFoundError` | No camera found | Connect camera, check device manager |
| `NotReadableError` | Camera in use | Close other apps, restart browser |
| `OverconstrainedError` | Unsupported settings | Lower resolution/frame rate |
| `SecurityError` | HTTPS required | Use HTTPS or localhost |
| `AbortError` | Hardware issue | Restart computer, try different USB port |

### Common User Messages

#### "No face detected"
- Ensure face is visible and well-lit
- Position face within camera frame
- Remove obstructions (masks, sunglasses)

#### "Camera failed to start"
- Check browser permissions
- Try different browser
- Restart computer

#### "Model loading failed"
- Check internet connection
- Try different CDN
- Clear browser cache

---

## 8. Preventive Measures

### For Users

1. **Regular Maintenance**
   - Clear browser cache weekly
   - Update browser to latest version
   - Restart device regularly

2. **Lighting Best Practices**
   - Face natural light source
   - Avoid backlighting
   - Ensure 300+ lux illumination
   - Avoid fluorescent lighting (causes flickering)

3. **Camera Care**
   - Clean lens with microfiber cloth
   - Check for obstructions
   - Update camera drivers

### For Administrators

1. **Server Configuration**
   ```nginx
   # Enable HTTPS
   server {
       listen 443 ssl;
       ssl_certificate /path/to/cert.pem;
       ssl_certificate_key /path/to/key.pem;
       
       # CORS headers for CDN
       add_header 'Access-Control-Allow-Origin' '*';
   }
   ```

2. **Monitoring**
   - Log face recognition errors
   - Monitor CDN response times
   - Track success/failure rates

3. **Documentation**
   - Provide user guides
   - Document troubleshooting steps
   - Create video tutorials

### Testing Checklist

Before going live:
- [ ] Test on Chrome (desktop)
- [ ] Test on Firefox (desktop)
- [ ] Test on Safari (desktop)
- [ ] Test on Chrome (mobile)
- [ ] Test on Safari (mobile)
- [ ] Test on low-end devices
- [ ] Test with poor lighting
- [ ] Test with different face orientations
- [ ] Test with accessories (glasses, etc.)
- [ ] Verify HTTPS/SSL certificate
- [ ] Test CDN fallback

---

## Quick Diagnostic Tool

Add this to browser console to test:

```javascript
// Test camera access
async function testCamera() {
    console.log('Testing camera access...');
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        console.log('✓ Camera access granted');
        stream.getTracks().forEach(track => track.stop());
        return true;
    } catch (error) {
        console.error('✗ Camera access failed:', error.name);
        return false;
    }
}

// Test model loading
async function testModels() {
    if (typeof faceapi === 'undefined') {
        console.error('✗ face-api.js not loaded');
        return false;
    }
    console.log('✓ face-api.js loaded');
    return true;
}

// Run tests
testCamera();
testModels();
```

---

## Contact Support

If issues persist after following this guide:

1. **Gather Information**
   - Browser name and version
   - Operating system
   - Error messages from console
   - Device model (for mobile)

2. **Screenshots**
   - Browser console (F12)
   - Site permissions page
   - Error messages

3. **Submit Ticket**
   - Include all gathered information
   - Describe troubleshooting steps taken
   - Note success on other devices if applicable
