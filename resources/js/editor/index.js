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
        showSendModal: false,
        showReadOnlyModal: false,
        showSuccessModal: false,
        showConfirmModal: false,
        alertMessage: '',
        confirmTitle: '',
        confirmMessage: '',
        confirmCallback: null,
        groups: [],
        logs: [],
        loadingLogs: false,
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
                signerPosition: '', signerName: '',
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
                await this.loadLogs();
            } else {
                // 'new' is no longer supported directly, must create via dashboard
                window.location.href = '/dashboard';
            }
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
            const element = document.getElementById('paperContent');
            const fileName = (this.document.title || 'Dokumen') + '.pdf';

            const opt = {
                margin: 0,
                filename: fileName,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    scrollY: 0,
                    scrollX: 0
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Ensure window.html2pdf is available (from app.js)
            if (window.html2pdf) {
                window.html2pdf().set(opt).from(element).save();
            } else {
                this.alertMessage = 'HTML2PDF library not loaded.';
                this.showSuccessModal = true;
            }
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
