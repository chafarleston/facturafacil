<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcación de Asistencia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .marker-card {
            background: #fff;
            border-radius: 24px;
            padding: 28px 34px;
            box-shadow: 0 20px 60px rgba(0,0,0,.4);
            width: 880px;
            max-width: 96vw;
            text-align: center;
        }
        .clock { font-size: 44px; font-weight: 700; color: #2c5364; margin-bottom: 8px; }
        h2 { font-weight: 700; color: #203a43; }

        /* Vista inicial (botón grande) */
        #homeView .btn-home {
            font-size: 34px;
            font-weight: 700;
            padding: 30px 40px;
            border-radius: 20px;
            margin-top: 18px;
            width: 100%;
        }

        /* Proceso en horizontal */
        .process-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        #videoCam {
            width: 420px;
            max-width: 100%;
            border-radius: 14px;
            border: 2px solid #2c5364;
            background: #000;
        }
        #camStatus { font-size: 14px; color: #888; margin-top: 6px; }
        .controls {
            display: flex;
            flex-direction: column;
            gap: 14px;
            align-items: center;
            min-width: 240px;
        }
        #dniInput {
            font-size: 30px;
            text-align: center;
            letter-spacing: 5px;
            font-weight: 700;
            height: 60px;
            border: 2px solid #2c5364;
            border-radius: 12px;
            width: 100%;
        }
        .btn-marcar {
            font-size: 20px;
            font-weight: 700;
            padding: 14px 40px;
            border-radius: 12px;
        }
        #idleInfo { font-size: 14px; color: #a00; }

        /* Confirmación / éxito en HORIZONTAL */
        #feedback {
            margin-top: 16px;
            border-radius: 14px;
            display: none;
            padding: 16px;
        }
        #feedback.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        #feedback.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 18px;
        }
        .info-chip {
            background: #fff;
            border: 1px solid #c3e6cb;
            border-radius: 12px;
            padding: 10px 16px;
            min-width: 130px;
        }
        .info-chip .lbl { font-size: 12px; color: #666; }
        .info-chip .val { font-size: 18px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="marker-card">
        <div class="clock" id="clock"></div>

        <!-- PASO 1: Botón grande de inicio -->
        <div id="homeView">
            <h2>MARCACIÓN DE ASISTENCIA</h2>
            <button class="btn btn-success btn-home" onclick="startProcess()">Iniciar un Proceso de Asistencia</button>
        </div>

        <!-- PASO 2: Proceso de verificación -->
        <div id="processView" style="display:none;">
            <div class="process-row">
                <div id="camWrap" style="display:none;">
                    <video id="videoCam" autoplay muted playsinline></video>
                    <div id="camStatus">Iniciando cámara...</div>
                </div>
                <div class="controls">
                    <div id="dniWrap" style="display:none;">
                        <input type="tel" id="dniInput" class="form-control" maxlength="8" inputmode="numeric" autocomplete="off" placeholder="DNI">
                    </div>
                    <button class="btn btn-success btn-marcar" id="btnMarcar" onclick="marcar()">MARCAR</button>
                    <div id="idleInfo">Volverá al inicio en <span id="idleCount">30</span>s</div>
                </div>
            </div>
            <div id="feedback"></div>
        </div>
    </div>

    <script src="{{ asset('js/face-api.min.js') }}"></script>
    <script>
        const MODEL_URL = '{{ asset('models/face-api') }}';
        const MODO = '{{ $modo ?? 'dni' }}';
        const IDLE_MS = 30000;   // 30s sin proceso -> volver al inicio
        const SUCCESS_MS = {{ ($exitoSegundos ?? 20) * 1000 }}; // éxito configurable (ms) -> volver al inicio

        let stream = null;
        let cameraAvailable = false;
        let modelsReady = false;
        let modelError = false;
        let lastCaptureError = null;
        let idleTimer = null;
        let idleSeconds = IDLE_MS / 1000;

        function setCamStatus(msg) {
            const st = document.getElementById('camStatus');
            if (st && MODO !== 'dni') st.textContent = msg;
        }

        async function loadFaceModels() {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                modelsReady = true;
            } catch (e) {
                modelError = true;
                modelsReady = false;
            }
        }

        function waitForModels(timeoutMs) {
            return new Promise(resolve => {
                if (modelsReady || modelError) return resolve(modelsReady);
                const start = Date.now();
                const iv = setInterval(() => {
                    if (modelsReady || modelError || Date.now() - start > timeoutMs) {
                        clearInterval(iv);
                        resolve(modelsReady);
                    }
                }, 250);
            });
        }

        function updateClock() {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // ---------- Cámara: solo se enciende cuando se inicia el proceso ----------
        async function startCamera() {
            if (MODO === 'dni' || cameraAvailable) return;
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280, max: 1920 },
                        height: { ideal: 720, max: 1080 },
                        facingMode: 'user'
                    },
                    audio: false
                });
                const video = document.getElementById('videoCam');
                video.srcObject = stream;
                await video.play();
                cameraAvailable = true;
                setCamStatus('Cámara lista');
            } catch (e) {
                cameraAvailable = false;
                setCamStatus('Cámara no disponible (marcación sin foto/verificación facial)');
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            cameraAvailable = false;
            const video = document.getElementById('videoCam');
            if (video) video.srcObject = null;
        }

        // ---------- Pasos (inicio -> proceso -> inicio) ----------
        function showHome() {
            stopCamera();
            clearTimeout(idleTimer);
            document.getElementById('processView').style.display = 'none';
            document.getElementById('feedback').style.display = 'none';
            document.getElementById('homeView').style.display = 'block';
        }

        function startProcess() {
            document.getElementById('homeView').style.display = 'none';
            document.getElementById('processView').style.display = 'block';
            document.getElementById('feedback').style.display = 'none';
            document.getElementById('dniInput').value = '';

            const useCamera = MODO !== 'dni';
            document.getElementById('camWrap').style.display = useCamera ? 'block' : 'none';
            document.getElementById('dniWrap').style.display = (MODO === 'dni' || MODO === 'dni_webcam') ? 'block' : 'none';

            if (useCamera) startCamera();
            resetIdle();
        }

        function resetIdle() {
            clearTimeout(idleTimer);
            idleSeconds = IDLE_MS / 1000;
            const countEl = document.getElementById('idleCount');
            if (countEl) countEl.textContent = idleSeconds;
            idleTimer = setInterval(() => {
                idleSeconds--;
                if (countEl) countEl.textContent = idleSeconds;
                if (idleSeconds <= 0) {
                    clearInterval(idleTimer);
                    showHome();
                }
            }, 1000);
        }

        function showSuccessAndReturn() {
            clearInterval(idleTimer);
            setTimeout(showHome, SUCCESS_MS);
        }

        // ---------- Detección facial ----------
        async function captureAndVerify() {
            if (!cameraAvailable) return { foto: null, descriptor: null };
            try {
                const video = document.getElementById('videoCam');
                const makeCanvas = () => {
                    const hiRes = document.createElement('canvas');
                    const scaleW = Math.min(1, 720 / video.videoWidth);
                    hiRes.width = Math.round(video.videoWidth * scaleW);
                    hiRes.height = Math.round(video.videoHeight * scaleW);
                    hiRes.getContext('2d').drawImage(video, 0, 0, hiRes.width, hiRes.height);
                    return hiRes;
                };

                let detection = null;
                if (modelsReady) {
                    const deadline = Date.now() + 15000;
                    while (Date.now() < deadline) {
                        const frame = makeCanvas();
                        detection = await Promise.race([
                            faceapi.detectSingleFace(frame).withFaceLandmarks().withFaceDescriptor(),
                            new Promise(resolve => setTimeout(() => resolve(null), 6000))
                        ]);
                        if (detection) break;
                        await new Promise(r => setTimeout(r, 250));
                    }
                }

                const lastFrame = makeCanvas();
                const lowRes = document.createElement('canvas');
                const targetWidth = 480;
                const scale = targetWidth / lastFrame.width;
                lowRes.width = targetWidth;
                lowRes.height = Math.round(lastFrame.height * scale);
                lowRes.getContext('2d').drawImage(lastFrame, 0, 0, lowRes.width, lowRes.height);
                const fotoBase64 = lowRes.toDataURL('image/jpeg', 0.7);

                if (!detection) return { foto: fotoBase64, descriptor: null };
                return { foto: fotoBase64, descriptor: Array.from(detection.descriptor) };
            } catch (e) {
                lastCaptureError = String(e && e.message ? e.message : e);
                return { foto: null, descriptor: null };
            }
        }

        async function liveFaceCheck() {
            if (!cameraAvailable || !modelsReady || MODO === 'dni') return;
            try {
                const v = document.getElementById('videoCam');
                if (!v.videoWidth) return;
                const frame = document.createElement('canvas');
                const s = Math.min(1, 320 / v.videoWidth);
                frame.width = Math.round(v.videoWidth * s);
                frame.height = Math.round(v.videoHeight * s);
                frame.getContext('2d').drawImage(v, 0, 0, frame.width, frame.height);
                const det = await faceapi.detectSingleFace(frame);
                setCamStatus(det ? 'Cámara lista · ✓ Rostro detectado' : 'Cámara lista · Colóquese frente a la cámara');
            } catch (e) { /* silencioso */ }
        }
        setInterval(liveFaceCheck, 1500);

        // ---------- Marcar ----------
        async function marcar() {
            const dni = document.getElementById('dniInput').value.trim();
            const fb = document.getElementById('feedback');
            if (MODO !== 'webcam' && dni.length !== 8) {
                fb.className = 'error';
                fb.style.display = 'block';
                fb.innerHTML = '<strong>Ingrese un DNI de 8 dígitos</strong>';
                resetIdle();
                return;
            }

            const btn = document.getElementById('btnMarcar');
            btn.disabled = true;
            fb.className = '';
            fb.style.display = 'none';

            if (MODO !== 'dni' && !cameraAvailable) {
                fb.className = 'error';
                fb.style.display = 'block';
                fb.innerHTML = '<strong>Cámara no disponible.</strong> Abra este marcador por localhost o con el acceso directo que permite la cámara (flag de Chrome/HTTPS).';
                btn.disabled = false;
                resetIdle();
                return;
            }

            try {
                let foto = null;
                let descriptor = null;
                if (MODO !== 'dni') {
                    const okModels = await waitForModels(15000);
                    if (!okModels) {
                        fb.className = 'error';
                        fb.style.display = 'block';
                        fb.innerHTML = '<strong>Los modelos faciales no cargaron.</strong> Verifique la conexión de red.';
                        btn.disabled = false;
                        resetIdle();
                        return;
                    }
                    lastCaptureError = null;
                    const captured = await captureAndVerify();
                    foto = captured.foto;
                    descriptor = captured.descriptor;
                    if (!descriptor && lastCaptureError) {
                        fb.className = 'error';
                        fb.style.display = 'block';
                        fb.innerHTML = '<strong>Error de detección:</strong> ' + lastCaptureError;
                        btn.disabled = false;
                        resetIdle();
                        return;
                    }
                }

                const payload = { dni: dni, foto_base64: foto, descriptor: descriptor };
                if (MODO === 'dni') delete payload.descriptor;
                if (MODO === 'dni' || MODO === 'dni_webcam') payload.dni = dni;

                const res = await fetch('{{ url('/marcar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    // Información de éxito en HORIZONTAL (chips lado a lado)
                    fb.className = 'success';
                    fb.style.display = 'block';
                    let chips = '<div class="info-row">';
                    chips += chip('Trabajador', data.nombre || '-');
                    chips += chip('Evento', data.event === 'COMPLETADO' ? 'Completado' : (data.event || '-'));
                    chips += chip('Hora', data.hora || '-');
                    if (data.estado && data.event !== 'COMPLETADO') chips += chip('Estado', data.estado);
                    if (data.descuento > 0) chips += chip('Descuento', 'S/ ' + Number(data.descuento).toFixed(2));
                    chips += '</div>';
                    fb.innerHTML = chips;
                    document.getElementById('dniInput').value = '';
                    btn.disabled = false;
                    showSuccessAndReturn();
                } else {
                    fb.className = 'error';
                    fb.style.display = 'block';
                    fb.innerHTML = '<strong>' + (data.message || 'Error') + '</strong>';
                    if (data.distancia !== undefined) {
                        fb.innerHTML += '<div style="font-size:14px; margin-top:6px;">Distancia facial: ' + data.distancia + '</div>';
                    }
                    document.getElementById('dniInput').value = '';
                    btn.disabled = false;
                    resetIdle();
                }
            } catch (e) {
                fb.className = 'error';
                fb.style.display = 'block';
                fb.innerHTML = '<strong>Error de conexión o al procesar</strong>';
                btn.disabled = false;
                resetIdle();
            }
        }

        function chip(lbl, val) {
            return '<div class="info-chip"><div class="lbl">' + lbl + '</div><div class="val">' + val + '</div></div>';
        }

        document.getElementById('dniInput').addEventListener('keydown', e => { if (e.key === 'Enter') marcar(); });

        loadFaceModels().catch(() => { modelsReady = false; modelError = true; });
    </script>
</body>
</html>
