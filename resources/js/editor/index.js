import SignaturePad from 'signature_pad';

window.editorApp = function () {
    return {
        isEditable() {
            if (!this.currentUser || !this.document) return false;

            // Admin always editable
            if (this.currentUser.role === 'admin') return true;

            // Reviewer can edit if it's pending review or if they are the author
            if (this.currentUser.role === 'reviewer') {
                if (this.document.status === 'pending_review' || this.document.status === 'approved') return true;
                if (this.document.author_id == this.currentUser.id) return true;
                return true; // Reviewers are generally allowed to edit for now
            }

            // User (Staff) logic
            if (this.currentUser.role === 'user') {
                // If document is approved, it's locked for everyone (except maybe admin, handled above)
                if (this.document.status === 'approved') return false;

                // If current user is the author (Sender)
                if (this.document.author_id && this.document.author_id == this.currentUser.id) {
                    const status = this.document.status;
                    // Authors can only edit drafts, revisions, or if returned (received)
                    return status === 'draft' || status === 'needs_revision' || status === 'received';
                }
                
                // If not author (Receiver), allow edit/forward
                return true;
            }

            return false;
        },

        documentId: null,
        currentUser: null,
        token: null,
        saving: false,
        isDownloading: false,
        showSendModal: false,
        showReadOnlyModal: false,
        showSuccessModal: false,
        showConfirmModal: false,
        showSignatureModal: false,
        signatureTab: 'draw',
        uploadedSignatureData: null,
        signaturePad: null,
        alertMessage: '',
        confirmTitle: '',
        confirmMessage: '',
        confirmCallback: null,
        groups: [],
        logs: [],
        versions: [],
        workLogs: [],
        loadingLogs: false,
        loadingVersions: false,
        loadingWorkLogs: false,
        showVersionsModal: false,
        showHistoryModal: false, // Unified History Modal
        activeHistoryTab: 'status', // status, versions, work
        sessionStartTime: null,
        document: {
            title: '',
            type: 'nota', // Default
            status: 'draft',
            target_role: '',
            target_value: '',
            feedback: '',
            deadline: null,
            approvals: [],
            content_data: {
                // Shared
                docNumber: '',
                location: '',
                // Nota
                to: '', from: '', attachment: '', subject: '',
                basis: [''],
                content: '',
                date: '',
                division: '',
                signerPosition: '', signerName: '', signature: '',
                // SPPD
                weigh: '',
                remembers: [''],
                task: '', destination: '', transport: '',
                dateGo: '', dateBack: '',
                funding: '', report: '', closing: '',
                signDate: '',
                ccs: ['']
            }
        },

        async init() {
            const userData = localStorage.getItem('dof_user');
            const token = localStorage.getItem('dof_token');

            if (!userData || !token) {
                window.location.href = '/login';
                return;
            }
            this.currentUser = JSON.parse(userData);
            this.token = token;

            const path = window.location.pathname;
            this.documentId = path.split('/').pop();

            await this.loadGroups();

            if (this.documentId && this.documentId !== 'new') {
                await this.loadDocument();
                this.loadLogs(); // No await to parallelize
            } else {
                window.location.href = '/dashboard';
            }

            // Start Time Tracking if editable
            if (this.isEditable()) {
                this.sessionStartTime = new Date();
            }
            
            // Handle tab close/navigation
            window.addEventListener('beforeunload', () => {
                if (this.sessionStartTime) {
                    this.sendWorkLog(true);
                }
            });
        },

        async openHistoryModal() {
            this.showHistoryModal = true;
            this.loadLogs();
            this.loadVersions();
            this.loadWorkLogs();
        },

        get processedLogs() {
            if (!this.logs) return [];
            return this.logs.map(log => {
                // Find version matching the log's version number
                // Only link if the log action is relevant to content change (optional, but 'version' usually implies content)
                // or just link by number.
                const ver = (this.versions || []).find(v => v.version_number == log.version);
                return { ...log, linkedVersion: ver };
            });
        },

        findVersion(log) {
            if (!this.versions || this.versions.length === 0) return null;
            return this.versions.find(v => v.version_number == log.version);
        },

        // Time Tracking
        async sendWorkLog(isUnload = false) {
            if (!this.documentId || this.documentId === 'new' || !this.sessionStartTime) return;

            const endTime = new Date();
            const payload = {
                start_time: this.sessionStartTime.toISOString(),
                end_time: endTime.toISOString()
            };

            const url = `/api/documents/${this.documentId}/work-logs`;
            
            if (isUnload) {
                // Use fetch with keepalive for unloading
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload),
                    keepalive: true
                });
            } else {
                try {
                    await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + this.token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    // Reset timer after successful log
                    this.sessionStartTime = new Date();
                } catch (e) {
                    console.error("Failed to log work time", e);
                }
            }
        },

        async loadWorkLogs() {
             if (!this.documentId || this.documentId === 'new') return;
            this.loadingWorkLogs = true;
            try {
                const response = await fetch(`/api/documents/${this.documentId}/work-logs`, {
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    }
                });
                if (response.ok) this.workLogs = await response.json();
            } catch (e) { console.error(e); }
            finally { this.loadingWorkLogs = false; }
        },

        // Versioning
        async loadVersions() {
            if (!this.documentId || this.documentId === 'new') return;
            this.loadingVersions = true;
            try {
                const response = await fetch(`/api/documents/${this.documentId}/versions`, {
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    }
                });
                if (response.ok) this.versions = await response.json();
            } catch (e) { console.error(e); }
            finally { this.loadingVersions = false; }
        },

        async restoreVersion(versionId) {
            if (!confirm('Apakah Anda yakin ingin mengembalikan dokumen ke versi ini? Perubahan saat ini akan tersimpan sebagai versi baru.')) return;

            try {
                const response = await fetch(`/api/documents/${this.documentId}/versions/${versionId}/restore`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    this.document = result.document;
                    // Fix structure if needed
                    if (this.document.status && typeof this.document.status === 'object' && this.document.status.value) {
                         this.document.status = this.document.status.value;
                    }
                    if (!this.document.content_data || Array.isArray(this.document.content_data)) {
                        this.document.content_data = {};
                    }
                    
                    this.alertMessage = 'Dokumen berhasil dipulihkan!';
                    this.showSuccessModal = true;
                    this.showHistoryModal = false;
                    
                    this.loadLogs();
                    this.loadVersions();
                    this.loadWorkLogs();
                }
            } catch (e) {
                console.error(e);
                this.alertMessage = 'Gagal memulihkan versi.';
                this.showSuccessModal = true;
            }
        },

        initSignaturePad() {
            this.showSignatureModal = true;
            this.signatureTab = 'draw';
            this.uploadedSignatureData = null;

            this.$nextTick(() => {
                const canvas = document.getElementById('signature-canvas');
                if (canvas) {
                    // Adjust canvas ratio for high DPI screens
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);

                    this.signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgba(255, 255, 255, 0)', // Transparent
                        penColor: 'rgb(0, 0, 0)'
                    });
                }
            });
        },

        handleSignatureUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) { // 2MB limit
                this.alertMessage = 'Ukuran gambar terlalu besar (maks 2MB)';
                this.showSuccessModal = true;
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                this.uploadedSignatureData = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        clearSignature() {
            if (this.signatureTab === 'draw' && this.signaturePad) {
                this.signaturePad.clear();
            } else if (this.signatureTab === 'upload') {
                this.uploadedSignatureData = null;
                // Reset input
                const input = document.getElementById('signature-upload-input');
                if (input) input.value = '';
            }
        },

        saveSignature() {
            let data = null;

            if (this.signatureTab === 'draw') {
                if (this.signaturePad && !this.signaturePad.isEmpty()) {
                    data = this.signaturePad.toDataURL('image/png');
                }
            } else if (this.signatureTab === 'upload') {
                if (this.uploadedSignatureData) {
                    data = this.uploadedSignatureData;
                }
            }

            if (data) {
                this.document.content_data.signature = data;
                this.showSignatureModal = false;
            } else {
                this.alertMessage = 'Tanda tangan masih kosong!';
                this.showSuccessModal = true;
            }
        },

        removeSignature() {
            this.document.content_data.signature = '';
        },

        async loadGroups() {
            try {
                const response = await fetch('/api/groups', {
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    }
                });
                if (response.ok) this.groups = await response.json();
            } catch (e) { console.error(e); }
        },

        async loadLogs() {
            if (!this.documentId || this.documentId === 'new') return;
            this.loadingLogs = true;
            try {
                const response = await fetch(`/api/documents/${this.documentId}/logs`, {
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    }
                });
                if (response.ok) this.logs = await response.json();
            } catch (e) { console.error(e); }
            finally { this.loadingLogs = false; }
        },

        async loadDocument() {
            try {
                const response = await fetch(`/api/documents/${this.documentId}`, {
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    }
                });
                if (response.ok) {
                    const doc = await response.json();
                    
                    // Normalize status if it's an object (Enum)
                    if (doc.status && typeof doc.status === 'object' && doc.status.value) {
                        doc.status = doc.status.value;
                    }

                    this.document = doc;

                    // Show read-only notice if user is staff and document is locked
                    if (!this.isEditable() && this.currentUser.role === 'user') {
                        this.showReadOnlyModal = true;
                    }

                    // Ensure content_data is an object (fix for empty array issue)
                    if (!this.document.content_data || Array.isArray(this.document.content_data)) {
                        this.document.content_data = {};
                    }

                    // Ensure content_data has arrays initialized if they were null
                    if (!this.document.content_data.basis) this.document.content_data.basis = [''];
                    if (!this.document.content_data.remembers) this.document.content_data.remembers = [''];
                    if (!this.document.content_data.ccs) this.document.content_data.ccs = [''];
                }
            } catch (e) { console.error(e); }
        },

        addListItem(key) {
            if (!this.document.content_data[key]) this.document.content_data[key] = [];
            this.document.content_data[key].push('');
        },

        removeListItem(key, index) {
            if (this.document.content_data[key].length > 0) {
                this.document.content_data[key].splice(index, 1);
            }
        },

        async confirmSend() {
            if (!this.document.target_role) {
                this.alertMessage = 'Pilih tujuan pengiriman!';
                this.showSuccessModal = true;
                return;
            }
            if (this.document.target_role === 'group' && !this.document.target_value) {
                this.alertMessage = 'Pilih group tujuan!';
                this.showSuccessModal = true;
                return;
            }

            // Set status based on target
            if (this.document.target_role === 'group') {
                this.document.status = 'sent';
            } else if (this.document.target_role === 'dispo') {
                this.document.status = 'pending_review';
            }

            this.showSendModal = false;

            // Pass 'false' for redirect, and 'true' for force save (bypass isEditable check)
            const success = await this.saveDocument(false, true);

            if (success) {
                window.location.href = '/dashboard?success=sent';
            }
        },

        async finishDocument() {
            this.confirmTitle = 'Selesaikan Dokumen?';
            this.confirmMessage = 'Apakah Anda yakin ingin menyelesaikan dokumen ini? Dokumen tidak dapat diedit atau diteruskan lagi.';
            this.confirmCallback = async () => {
                this.showConfirmModal = false;
                this.document.status = 'approved';
                
                const success = await this.saveDocument(false, true);

                if (success) {
                    this.alertMessage = 'Dokumen berhasil diselesaikan (ACC).';
                    this.showSuccessModal = true;
                    setTimeout(() => window.location.reload(), 1500);
                }
            };
            this.showConfirmModal = true;
        },

        async saveDocument(redirectOnCreate = true, force = false) {
            // Allow save if force is true, or if editable, or if admin
            if (!force && !this.isEditable() && this.currentUser.role !== 'admin') {
                return false;
            }
            this.saving = true;
            try {
                const url = this.document.id ? `/api/documents/${this.document.id}` : '/api/documents';
                const method = this.document.id ? 'PUT' : 'POST';

                // Construct payload
                const payload = {
                    title: this.document.title,
                    type: this.document.type,
                    status: this.document.status,
                    content_data: this.document.content_data,
                    deadline: this.document.deadline || null,
                    approvals: this.document.approvals,
                    target: {
                        type: this.document.target_role,
                        value: this.document.target_value
                    }
                };

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    const result = await response.json();

                    if (!this.document.id && result.id) {
                        if (redirectOnCreate) {
                            window.location.href = `/editor/${result.id}`;
                            return true;
                        }
                    } else {
                        if (redirectOnCreate) {
                            this.alertMessage = 'Dokumen berhasil disimpan!';
                            this.showSuccessModal = true;
                        }
                    }

                    this.document = result.document || this.document; // Update local state if returned
                    this.documentId = result.id || this.documentId;
                    
                    // Log work time
                    await this.sendWorkLog();
                    
                    // Reload logs after save to show updated history
                    await this.loadLogs();
                    
                    return true;
                }
                return false;
            } catch (error) {
                this.alertMessage = 'Gagal menyimpan dokumen.';
                this.showSuccessModal = true;
                console.error(error);
                return false;
            } finally {
                this.saving = false;
            }
        },

        async updateStatus(newStatus) {
            try {
                const response = await fetch(`/api/documents/${this.document.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        feedback: this.document.feedback
                    })
                });
                if (response.ok) {
                    this.alertMessage = 'Status berhasil diperbarui!';
                    this.showSuccessModal = true;
                    setTimeout(() => window.location.href = '/dashboard', 1000);
                }
            } catch (e) { console.error(e); }
        },

        downloadPDF() {
            if (!this.document.id) {
                this.alertMessage = 'Harap simpan dokumen terlebih dahulu sebelum mencetak.';
                this.showSuccessModal = true;
                return;
            }
            // Open the print view in a new tab. The print view has onload="window.print()"
            window.open(`/documents/${this.document.id}/print`, '_blank');
        },

        formatDate(dateStr) {
            if (!dateStr) return '...';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        getStatusLabel(status) {
            const labels = {
                draft: 'Draft',
                pending_review: 'Review',
                needs_revision: 'Revisi',
                approved: 'Approved',
                sent: 'Dikirim',
                received: 'Diterima'
            };
            return labels[status] || 'Draft';
        },

        formatDeadlineDisplay(deadline) {
            if (!deadline) return '';

            const date = new Date(deadline);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
