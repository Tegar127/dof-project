window.editorApp = function() {
    return {
        isEditable() {
            if (!this.currentUser) return false;
            
            // Admin and Reviewer always editable
            if (this.currentUser.role === 'admin' || this.currentUser.role === 'reviewer') return true;
            
            // User (Staff) logic
            if (this.currentUser.role === 'user') {
                // If current user is the author (Sender)
                if (this.document.author_id && this.document.author_id == this.currentUser.id) {
                     const status = this.document.status;
                     return status === 'draft' || status === 'needs_revision';
                }
                // If not author (Receiver), allow edit
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
        groups: [],
        document: {
            title: '',
            type: 'nota', // Default
            status: 'draft',
            target_role: '',
            target_value: '',
            feedback: '',
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
            } else {
                const newDocData = localStorage.getItem('dof_new_doc');
                if (newDocData) {
                    const data = JSON.parse(newDocData);
                    this.document.type = data.type;
                    this.document.title = data.name;
                    localStorage.removeItem('dof_new_doc');
                }
                // Set default dates for new doc
                const today = new Date().toISOString().split('T')[0];
                this.document.content_data.date = today;
                this.document.content_data.signDate = today;
                this.document.content_data.dateGo = today;
                this.document.content_data.dateBack = today;
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
                    this.document = doc;
                    
                    // Show read-only notice if user is staff and document is locked
                    if (!this.isEditable() && this.currentUser.role === 'user') {
                        this.showReadOnlyModal = true;
                    }

                    // Ensure content_data has arrays initialized if they were null
                    if(!this.document.content_data) this.document.content_data = {};
                    if(!this.document.content_data.basis) this.document.content_data.basis = [''];
                    if(!this.document.content_data.remembers) this.document.content_data.remembers = [''];
                    if(!this.document.content_data.ccs) this.document.content_data.ccs = [''];
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
                alert('Pilih tujuan pengiriman!');
                return;
            }
            if (this.document.target_role === 'group' && !this.document.target_value) {
                alert('Pilih group tujuan!');
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
                        if (redirectOnCreate) alert('Dokumen berhasil disimpan!');
                    }
                    
                    this.document.id = result.id; // Ensure ID is set
                    this.documentId = result.id;
                    return true;
                }
                return false;
            } catch (error) {
                alert('Gagal menyimpan dokumen.');
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
                    alert('Status berhasil diperbarui!');
                    window.location.href = '/dashboard';
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
                alert('HTML2PDF library not loaded.');
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
        }
    }
}
