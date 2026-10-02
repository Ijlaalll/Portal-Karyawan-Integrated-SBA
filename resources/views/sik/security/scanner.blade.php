@extends('layouts.app')

@section('title', 'Scanner SIK - Pos Security SBA')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('sik.scan-logs') }}" class="text-xs text-[#2563EB] hover:underline font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                    Riwayat Scan
                </a>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs text-slate-500 font-medium">Validasi Pos Security</span>
            </div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Scanner QR Surat Izin Keluar (SIK)
            </h1>
            <p class="text-sm text-[#475569] mt-1">Validasi izin keluar dan catat kepulangan karyawan di Pos Gerbang Security SBA.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sik.security.dashboard') }}" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start sm:self-auto cursor-pointer border border-slate-300">
                <span class="material-symbols-outlined text-[18px] text-slate-500">dashboard</span>
                Dashboard Security
            </a>
            <a href="{{ route('sik.scan-logs') }}" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start sm:self-auto cursor-pointer border border-slate-300">
                <span class="material-symbols-outlined text-[18px] text-slate-500">history</span>
                Riwayat SIK
            </a>
        </div>
    </header>

    <main class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Scanner View & Manual Input (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <!-- Camera QR Scanner Viewport -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-md text-white text-center">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        Kamera Scanner
                    </span>
                    <button id="toggleCameraBtn" onclick="toggleCamera()" class="text-xs text-blue-400 hover:text-blue-300 font-semibold">
                        Buka Kamera
                    </button>
                </div>

                <div class="w-full bg-slate-950 rounded-xl overflow-hidden min-h-[220px] flex items-center justify-center border border-slate-800 relative">
                    <div id="scannerPlaceholder" class="p-6 text-center text-slate-400 space-y-2">
                        <span class="material-symbols-outlined text-5xl text-slate-600">qr_code_scanner</span>
                        <p class="text-xs">Arahkan kamera ke QR Code Surat Izin Keluar atau gunakan input manual di bawah.</p>
                        <button type="button" onclick="startScanner()" 
                            class="mt-2 px-4 py-2 text-white rounded-lg text-xs font-semibold shadow-sm hover:bg-blue-700 transition-colors inline-flex items-center gap-2 cursor-pointer"
                            style="background-color: #2563EB;">
                            <span class="material-symbols-outlined text-[18px]">photo_camera</span>
                            Aktifkan Kamera
                        </button>
                    </div>
                    <div id="reader" class="w-full hidden"></div>
                </div>
            </div>

            <!-- Manual Token Form -->
            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-blue-600">keyboard</span>
                        Input Manual Token / SIK
                    </h3>
                    <span class="text-[10px] text-slate-400 font-mono bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Tekan Enter ↵</span>
                </div>
                <form id="manualScanForm" onsubmit="handleManualScan(event)" class="space-y-3">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[18px]">search</span>
                            </div>
                            <input type="text" id="tokenInput" placeholder="Ketik token QR / nomor SIK..." required autocomplete="off"
                                class="w-full rounded-xl pl-10 pr-3 py-2.5 text-xs font-mono text-slate-900 bg-slate-50/60 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all placeholder:text-slate-400"
                                style="border: 1px solid #CBD5E1;">
                        </div>
                        <button type="submit" id="btnCekToken" 
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-white text-xs font-semibold hover:bg-blue-700 transition-colors shadow-sm shrink-0 cursor-pointer"
                            style="background-color: #2563EB;">
                            <span class="material-symbols-outlined text-[17px]">verified</span>
                            <span>Cek Validasi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Real-time Scan Result & Confirmation (7 cols) -->
        <div class="lg:col-span-7">
            <!-- Empty Placeholder -->
            <div id="emptyResultState" class="bg-white border border-[#E5E7EB] rounded-2xl p-12 text-center shadow-sm flex flex-col items-center justify-center min-h-[360px]">
                <div class="p-4 bg-slate-50 rounded-full text-slate-300 mb-3">
                    <span class="material-symbols-outlined text-6xl">badge</span>
                </div>
                <h3 class="text-base font-bold text-slate-800">Menunggu Pemindaian QR</h3>
                <p class="text-xs text-slate-500 max-w-sm mt-1">Scan QR Code SIK karyawan atau masukkan token pada kolom manual untuk melihat data validasi gate.</p>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="hidden bg-white border border-[#E5E7EB] rounded-2xl p-12 text-center shadow-sm flex flex-col items-center justify-center min-h-[360px]">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mb-3"></div>
                <p class="text-sm font-semibold text-slate-700">Memvalidasi izin keluar di server...</p>
            </div>

            <!-- Error Result Card -->
            <div id="errorResultCard" class="hidden bg-white border-2 border-red-300 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 text-red-600">
                    <span class="material-symbols-outlined text-4xl">error</span>
                    <div>
                        <h3 class="text-base font-bold">QR Code Tidak Valid / Gagal</h3>
                        <p id="errorMessageText" class="text-xs text-red-700 mt-0.5"></p>
                    </div>
                </div>
                <button type="button" onclick="resetScannerUI()" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                    Scan Ulang
                </button>
            </div>

            <!-- Valid Result Card -->
            <div id="validResultCard" class="hidden bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-md space-y-5">
                <!-- Inline Success Banner -->
                <div id="saveSuccessBanner" class="hidden p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl text-emerald-600 shrink-0">check_circle</span>
                    <div>
                        <strong class="block font-bold text-emerald-950 text-sm">Scan Berhasil Disimpan!</strong>
                        <span id="saveSuccessText" class="text-emerald-800"></span>
                    </div>
                </div>

                <!-- Inline Error Banner -->
                <div id="saveErrorBanner" class="hidden p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl text-rose-600 shrink-0">error</span>
                    <div>
                        <strong class="block font-bold text-rose-950 text-sm">Gagal Menyimpan Scan</strong>
                        <span id="saveErrorText" class="text-rose-800"></span>
                    </div>
                </div>

                <!-- Action Header -->
                <div id="actionHeaderBanner" class="p-4 rounded-xl flex items-center justify-between">
                    <div>
                        <span id="scanActionBadge" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider"></span>
                        <h2 id="resultUserName" class="text-xl font-bold text-slate-900 mt-1"></h2>
                        <p id="resultSikNum" class="text-xs font-mono text-slate-600"></p>
                    </div>
                    <div id="overdueAlertBadge" class="hidden text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                            TERLAMBAT KEMBALI
                        </span>
                    </div>
                </div>

                <!-- Snapshot Data Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div>
                        <span class="text-slate-400 block">Nomor Karyawan:</span>
                        <span id="resultEmpId" class="font-semibold text-slate-800"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Departemen:</span>
                        <span id="resultDept" class="font-semibold text-slate-800"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jabatan:</span>
                        <span id="resultPos" class="font-semibold text-slate-800"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jenis Keperluan:</span>
                        <span id="resultPurposeType" class="font-bold"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Rencana Keluar:</span>
                        <span id="resultExpDepart" class="font-semibold text-slate-800"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Rencana Kembali:</span>
                        <span id="resultExpReturn" class="font-semibold text-slate-800"></span>
                    </div>
                </div>

                <!-- Keperluan Detail -->
                <div class="text-xs space-y-1">
                    <span class="text-slate-400 block font-semibold">Keperluan:</span>
                    <p id="resultPurpose" class="font-bold text-slate-800 text-sm"></p>
                    <p id="resultPurposeDetail" class="text-slate-600 bg-slate-50 p-2 rounded border border-slate-200"></p>
                </div>

                <!-- Kendaraan -->
                <div class="text-xs flex justify-between p-2.5 bg-blue-50/50 rounded-lg border border-blue-100">
                    <span class="text-slate-600">Kendaraan: <strong id="resultVehicle" class="text-blue-950"></strong></span>
                    <span class="text-slate-600">Approver: <strong id="resultApprover" class="text-slate-800"></strong></span>
                </div>

                <!-- Execution Form -->
                <form id="confirmActionForm" onsubmit="submitGateConfirmation(event)" class="space-y-4 pt-3 border-t border-slate-200">
                    @csrf
                    <input type="hidden" id="currentPermitId">
                    <input type="hidden" id="currentAction">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pos Gerbang / Gate</label>
                            <input type="text" id="gateInput" value="Pos Utama / Main Gate" required class="w-full rounded-lg p-2 text-xs text-[#0F172A]" style="border: 1px solid #CBD5E1;">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Security (Opsional)</label>
                            <input type="text" id="notesInput" placeholder="Kondisi barang / kendaraan..." class="w-full rounded-lg p-2 text-xs text-[#0F172A]" style="border: 1px solid #CBD5E1;">
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="resetScannerUI()" 
                            class="w-1/3 py-3 rounded-xl text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors cursor-pointer"
                            style="background-color: #F8FAFC; border: 1px solid #CBD5E1;">
                            Batal / Scan Lain
                        </button>

                        <button type="submit" id="confirmBtn" style="background-color: #2563EB;" class="w-2/3 py-3 rounded-xl text-white text-sm font-semibold shadow-sm transition-all flex items-center justify-center gap-2 hover:bg-blue-700 cursor-pointer">
                            <span id="confirmBtnIcon" class="material-symbols-outlined text-[20px]">save</span>
                            <span id="confirmBtnText">Simpan Scan Keluar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<!-- Tailwind Safelist Helper for Dynamic JS Colors -->
<div class="hidden bg-purple-600 hover:bg-purple-700 bg-emerald-600 hover:bg-emerald-700 bg-slate-600 hover:bg-slate-700 bg-indigo-600 hover:bg-indigo-700"></div>

<!-- HTML5-QRCode Scanner Script (Self-hosted for guaranteed offline intranet gate scanning) -->
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

<script>
    let html5QrCode = null;
    let isScanning = false;
    let isTransitioning = false;
    let isSubmitting = false;

    async function startScanner() {
        if (isScanning || isTransitioning) return;
        isTransitioning = true;

        const readerElement = document.getElementById('reader');
        const placeholderElement = document.getElementById('scannerPlaceholder');
        const toggleBtn = document.getElementById('toggleCameraBtn');

        if (toggleBtn) {
            toggleBtn.disabled = true;
            toggleBtn.textContent = "Menghubungkan...";
        }

        try {
            // Sembunyikan placeholder, tampilkan dan bersihkan container reader
            if (placeholderElement) placeholderElement.classList.add('hidden');
            if (readerElement) {
                readerElement.classList.remove('hidden');
                readerElement.innerHTML = "";
            }

            // Inisialisasi scanner
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 220, height: 220 } };

            await html5QrCode.start(
                { facingMode: "environment" },
                config,
                (decodedText, decodedResult) => {
                    playBeep();
                    processToken(decodedText);
                    stopScanner();
                },
                (errorMessage) => {}
            );

            isScanning = true;
            if (toggleBtn) {
                toggleBtn.disabled = false;
                toggleBtn.textContent = "Matikan Kamera";
            }
        } catch (err) {
            console.error("Gagal start scanner:", err);
            cleanupScanner();
            alert("Tidak dapat mengakses kamera: " + err);
        } finally {
            isTransitioning = false;
            if (toggleBtn) toggleBtn.disabled = false;
        }
    }

    async function stopScanner() {
        if (!isScanning && !html5QrCode) {
            cleanupScanner();
            return;
        }
        if (isTransitioning) return;
        isTransitioning = true;

        const toggleBtn = document.getElementById('toggleCameraBtn');
        if (toggleBtn) {
            toggleBtn.disabled = true;
            toggleBtn.textContent = "Mematikan...";
        }

        try {
            if (html5QrCode) {
                if (html5QrCode.isScanning) {
                    await html5QrCode.stop();
                }
                try {
                    await html5QrCode.clear();
                } catch (clearErr) {}
                html5QrCode = null;
            }
        } catch (err) {
            console.error("Error stopping scanner:", err);
        } finally {
            cleanupScanner();
            isTransitioning = false;
            if (toggleBtn) toggleBtn.disabled = false;
        }
    }

    function cleanupScanner() {
        isScanning = false;
        html5QrCode = null;

        const readerElement = document.getElementById('reader');
        const placeholderElement = document.getElementById('scannerPlaceholder');
        const toggleBtn = document.getElementById('toggleCameraBtn');

        if (readerElement) {
            readerElement.innerHTML = "";
            readerElement.classList.add('hidden');
        }
        if (placeholderElement) {
            placeholderElement.classList.remove('hidden');
        }
        if (toggleBtn) {
            toggleBtn.textContent = "Buka Kamera";
        }
    }

    function toggleCamera() {
        if (isScanning) {
            stopScanner();
        } else {
            startScanner();
        }
    }

    function handleManualScan(e) {
        e.preventDefault();
        const token = document.getElementById('tokenInput').value.trim();
        if (token) {
            processToken(token);
        }
    }

    function processToken(token) {
        showLoading();
        setBtnCekLoading(true);

        fetch("{{ route('sik.scanner.scan') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ qr_token: token })
        })
        .then(res => res.json())
        .then(data => {
            if (data.valid) {
                showValidResult(data);
            } else {
                showErrorResult(data.message || 'Token tidak valid');
            }
        })
        .catch(err => {
            showErrorResult("Terjadi kesalahan jaringan atau server.");
        })
        .finally(() => {
            setBtnCekLoading(false);
        });
    }

    function setBtnCekLoading(isLoading) {
        const btn = document.getElementById('btnCekToken');
        if (!btn) return;
        if (isLoading) {
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin material-symbols-outlined text-[17px]">progress_activity</span><span>Memeriksa...</span>';
            btn.classList.add('opacity-80', 'cursor-not-allowed');
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-[17px]">verified</span><span>Cek Validasi</span>';
            btn.classList.remove('opacity-80', 'cursor-not-allowed');
        }
    }

    function showLoading() {
        document.getElementById('emptyResultState').classList.add('hidden');
        document.getElementById('errorResultCard').classList.add('hidden');
        document.getElementById('validResultCard').classList.add('hidden');
        document.getElementById('loadingState').classList.remove('hidden');
    }

    function showErrorResult(msg) {
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('validResultCard').classList.add('hidden');
        document.getElementById('errorMessageText').textContent = msg;
        document.getElementById('errorResultCard').classList.remove('hidden');
    }

    function showValidResult(data) {
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('errorResultCard').classList.add('hidden');
        document.getElementById('saveSuccessBanner').classList.add('hidden');
        document.getElementById('saveErrorBanner').classList.add('hidden');

        const p = data.permit;
        const isExit = (data.action === 'EXIT');

        document.getElementById('currentPermitId').value = p.id;
        document.getElementById('currentAction').value = data.action;

        // Reset submit state
        isSubmitting = false;
        const btn = document.getElementById('confirmBtn');
        const btnIcon = document.getElementById('confirmBtnIcon');
        const btnText = document.getElementById('confirmBtnText');
        const gateInput = document.getElementById('gateInput');
        const notesInput = document.getElementById('notesInput');

        btn.disabled = false;
        gateInput.disabled = false;
        notesInput.disabled = false;

        // Banner styling and button text based on action
        const banner = document.getElementById('actionHeaderBanner');
        const badge = document.getElementById('scanActionBadge');

        if (isExit) {
            banner.className = "p-4 rounded-xl flex items-center justify-between bg-blue-50 border border-blue-200";
            badge.className = "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-100 text-blue-900 border border-blue-200";
            badge.textContent = "VERIFIKASI KELUAR PABRIK";
            btn.style.backgroundColor = '#2563EB';
            btn.className = "w-2/3 py-3 rounded-xl text-white text-sm font-semibold shadow-sm transition-all flex items-center justify-center gap-2 hover:bg-blue-700 cursor-pointer";
            btnIcon.textContent = "save";
            btnText.textContent = "Simpan Scan Keluar";
        } else {
            banner.className = "p-4 rounded-xl flex items-center justify-between bg-emerald-50 border border-emerald-200";
            badge.className = "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-200";
            badge.textContent = "VERIFIKASI KEPULANGAN";
            btn.style.backgroundColor = '#059669';
            btn.className = "w-2/3 py-3 rounded-xl text-white text-sm font-semibold shadow-sm transition-all flex items-center justify-center gap-2 hover:bg-emerald-700 cursor-pointer";
            btnIcon.textContent = "save";
            btnText.textContent = "Simpan Scan Kembali";
        }

        if (data.is_overdue) {
            document.getElementById('overdueAlertBadge').classList.remove('hidden');
        } else {
            document.getElementById('overdueAlertBadge').classList.add('hidden');
        }

        document.getElementById('resultUserName').textContent = p.user_name;
        document.getElementById('resultSikNum').textContent = p.sik_number;
        document.getElementById('resultEmpId').textContent = p.employee_id;
        document.getElementById('resultDept').textContent = p.department;
        document.getElementById('resultPos').textContent = p.position;
        document.getElementById('resultPurposeType').textContent = p.purpose_type;
        document.getElementById('resultExpDepart').textContent = p.expected_departure_time + " WIB";
        document.getElementById('resultExpReturn').textContent = p.expected_return_time + " WIB";
        document.getElementById('resultPurpose').textContent = p.purpose;
        document.getElementById('resultPurposeDetail').textContent = p.purpose_detail || '-';
        document.getElementById('resultVehicle').textContent = p.vehicle_type + (p.vehicle_details !== '-' ? ' (' + p.vehicle_details + ')' : '');
        document.getElementById('resultApprover').textContent = p.approver_name;

        document.getElementById('validResultCard').classList.remove('hidden');
    }

    function submitGateConfirmation(e) {
        e.preventDefault();
        if (isSubmitting) return;

        const permitId = document.getElementById('currentPermitId').value;
        const action = document.getElementById('currentAction').value;
        const gate = document.getElementById('gateInput').value;
        const notes = document.getElementById('notesInput').value;

        const endpoint = (action === 'EXIT')
            ? `/sik-security/${permitId}/exit`
            : `/sik-security/${permitId}/return`;

        // UI Loading State on Button
        isSubmitting = true;
        const btn = document.getElementById('confirmBtn');
        const btnIcon = document.getElementById('confirmBtnIcon');
        const btnText = document.getElementById('confirmBtnText');

        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-not-allowed');
        btnIcon.className = "animate-spin material-symbols-outlined text-[20px]";
        btnIcon.textContent = "progress_activity";
        btnText.textContent = "Menyimpan Scan...";

        document.getElementById('saveSuccessBanner').classList.add('hidden');
        document.getElementById('saveErrorBanner').classList.add('hidden');

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ gate: gate, notes: notes })
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.success) {
                // SUCCESS STATE
                document.getElementById('saveSuccessText').textContent = data.message || "Scan berhasil disimpan ke riwayat security.";
                document.getElementById('saveSuccessBanner').classList.remove('hidden');

                // Update Button State to Saved
                btn.disabled = true;
                btn.style.backgroundColor = '#475569';
                btn.className = "w-2/3 py-3 rounded-xl text-white text-sm font-bold shadow-none transition-all flex items-center justify-center gap-2 bg-slate-600 cursor-not-allowed opacity-90";
                btnIcon.className = "material-symbols-outlined text-[20px]";
                btnIcon.textContent = "task_alt";
                btnText.textContent = "Tersimpan";

                document.getElementById('gateInput').disabled = true;
                document.getElementById('notesInput').disabled = true;
            } else {
                // ERROR STATE
                isSubmitting = false;
                btn.disabled = false;
                btn.classList.remove('opacity-70', 'cursor-not-allowed');
                btnIcon.className = "material-symbols-outlined text-[20px]";
                btnIcon.textContent = "save";
                btn.style.backgroundColor = (action === 'EXIT') ? '#2563EB' : '#059669';
                btnText.textContent = (action === 'EXIT') ? "Simpan Scan Keluar" : "Simpan Scan Kembali";

                const errMsg = data.error || data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : "Gagal menyimpan scan.");
                document.getElementById('saveErrorText').textContent = errMsg;
                document.getElementById('saveErrorBanner').classList.remove('hidden');
            }
        })
        .catch(err => {
            isSubmitting = false;
            btn.disabled = false;
            btn.classList.remove('opacity-70', 'cursor-not-allowed');
            btnIcon.className = "material-symbols-outlined text-[20px]";
            btnIcon.textContent = "save";
            btn.style.backgroundColor = (action === 'EXIT') ? '#2563EB' : '#059669';
            btnText.textContent = (action === 'EXIT') ? "Simpan Scan Keluar" : "Simpan Scan Kembali";

            document.getElementById('saveErrorText').textContent = "Terjadi kesalahan jaringan atau koneksi server.";
            document.getElementById('saveErrorBanner').classList.remove('hidden');
        });
    }

    function resetScannerUI() {
        isSubmitting = false;
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('errorResultCard').classList.add('hidden');
        document.getElementById('validResultCard').classList.add('hidden');
        document.getElementById('saveSuccessBanner').classList.add('hidden');
        document.getElementById('saveErrorBanner').classList.add('hidden');
        document.getElementById('emptyResultState').classList.remove('hidden');

        document.getElementById('tokenInput').value = '';
        document.getElementById('gateInput').value = 'Pos Utama / Main Gate';
        document.getElementById('gateInput').disabled = false;
        document.getElementById('notesInput').value = '';
        document.getElementById('notesInput').disabled = false;

        const btn = document.getElementById('confirmBtn');
        if (btn) btn.disabled = false;
    }

    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.value = 0.1;
            osc.start();
            setTimeout(() => { osc.stop(); ctx.close(); }, 120);
        } catch (e) {}
    }
</script>
@endsection
