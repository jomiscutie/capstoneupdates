/**
 * Copy Bootstrap and Bootstrap Icons from node_modules to public/vendor
 * so they can be served locally and work offline.
 * Run: npm run vendor:copy (after npm install)
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const publicDir = path.join(root, 'public', 'vendor');

function mkdir(dir) {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
}

function copyFile(src, dest) {
  mkdir(path.dirname(dest));
  fs.copyFileSync(src, dest);
  console.log('  ' + path.relative(root, dest));
}

// Bootstrap: dist/css and dist/js
const bootstrapDist = path.join(root, 'node_modules', 'bootstrap', 'dist');
if (fs.existsSync(bootstrapDist)) {
  console.log('Copying Bootstrap...');
  copyFile(
    path.join(bootstrapDist, 'css', 'bootstrap.min.css'),
    path.join(publicDir, 'bootstrap', 'css', 'bootstrap.min.css')
  );
  copyFile(
    path.join(bootstrapDist, 'js', 'bootstrap.bundle.min.js'),
    path.join(publicDir, 'bootstrap', 'js', 'bootstrap.bundle.min.js')
  );
} else {
  console.warn('Bootstrap not found in node_modules. Run: npm install');
}

// Bootstrap Icons: font/bootstrap-icons.css and font/fonts/*
const biFont = path.join(root, 'node_modules', 'bootstrap-icons', 'font');
if (fs.existsSync(biFont)) {
  console.log('Copying Bootstrap Icons...');
  copyFile(
    path.join(biFont, 'bootstrap-icons.css'),
    path.join(publicDir, 'bootstrap-icons', 'bootstrap-icons.css')
  );
  const fontsDir = path.join(biFont, 'fonts');
  if (fs.existsSync(fontsDir)) {
    const fonts = fs.readdirSync(fontsDir);
    const biFontsOut = path.join(publicDir, 'bootstrap-icons', 'fonts');
    mkdir(biFontsOut);
    fonts.forEach((f) => {
      copyFile(path.join(fontsDir, f), path.join(biFontsOut, f));
    });
  }
} else {
  console.warn('Bootstrap Icons not found in node_modules. Run: npm install');
}

// Face-api.js: dist script and model files (for offline registration/verification)
const faceApiRoot = path.join(root, 'node_modules', '@vladmandic', 'face-api');
const faceApiDist = path.join(faceApiRoot, 'dist');
const faceApiModel = path.join(faceApiRoot, 'model');
const faceApiOut = path.join(publicDir, 'face-api');
const faceApiModelOut = path.join(faceApiOut, 'model');

if (fs.existsSync(faceApiDist)) {
  console.log('Copying Face API...');
  // Use face-api.js (npm has .js; CDN min is built separately)
  const jsName = fs.existsSync(path.join(faceApiDist, 'face-api.min.js')) ? 'face-api.min.js' : 'face-api.js';
  copyFile(
    path.join(faceApiDist, jsName),
    path.join(faceApiOut, 'face-api.min.js')
  );
} else {
  console.warn('@vladmandic/face-api not found in node_modules. Run: npm install');
}

if (fs.existsSync(faceApiModel)) {
  console.log('Copying Face API models...');
  mkdir(faceApiModelOut);
  const modelFiles = fs.readdirSync(faceApiModel);
  const needed = [
    'tiny_face_detector_model.bin', 'tiny_face_detector_model-weights_manifest.json',
    'face_landmark_68_model.bin', 'face_landmark_68_model-weights_manifest.json',
    'face_recognition_model.bin', 'face_recognition_model-weights_manifest.json',
    'face_expression_model.bin', 'face_expression_model-weights_manifest.json'
  ];
  modelFiles.forEach((f) => {
    if (needed.includes(f)) {
      copyFile(path.join(faceApiModel, f), path.join(faceApiModelOut, f));
    }
  });
}

console.log('Vendor copy done. Use asset("vendor/...") in views.');
