window.dashboardApp = function() {
    return {
        currentUser: null,
        token: null,
        documents: [],
        filteredDocs: [],
        searchTerm: '',
        showCreateModal: false,
        showDeleteModal: false,
        docToDelete: null,
        documentName: '',
        documentType: null,
        showSuccessModal: false,
        alertMessage: '',

        async init() {
            // Check for success messages in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === 'sent') {
                this.alertMessage = 'Dokumen berhasil dikirim ke tujuan!';
                this.showSuccessModal = true;
                
                // Clean up URL without refreshing
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Check authentication
            const userData = localStorage.getItem('dof_user');
            const token = localStorage.getItem('dof_token');
            
            if (!userData || !token) {
                window.location.href = '/login';
                return;
            }

            this.currentUser = JSON.parse(userData);
            this.token = token;

            // Redirect admin to admin panel
            if (this.currentUser.role === 'admin') {
                window.location.href = '/admin';
                return;
            }

            // Load documents
            await this.loadDocuments();

            // Watch search term
            this.$watch('searchTerm', () => this.filterDocuments());
        },

        async loadDocuments() {
            try {
                const response = await fetch('/api/documents', {
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.documents = await response.json();
                    this.filterDocuments();
                }
            } catch (error) {
                console.error('Error loading documents:', error);
            }
        },

        filterDocuments() {
            let docs = this.documents;

            // Filter by search term
            if (this.searchTerm) {
                const search = this.searchTerm.toLowerCase();
                docs = docs.filter(d =>
                    d.title.toLowerCase().includes(search) ||
                    (d.content_data?.docNumber || d.data?.docNumber || '').toLowerCase().includes(search)
                );
            }

            this.filteredDocs = docs;
        },

        handleCreate(type) {
            this.documentType = type;
            this.showCreateModal = true;
        },

        confirmCreate() {
            if (!this.documentName.trim()) {
                alert('Nama dokumen tidak boleh kosong!');
                return;
            }
            
            localStorage.setItem('dof_new_doc', JSON.stringify({
                type: this.documentType,
                name: this.documentName
            }));

            window.location.href = '/editor/new';
        },

        handleDelete(docId, docTitle) {
            this.docToDelete = { id: docId, title: docTitle };
            this.showDeleteModal = true;
        },

        async confirmDelete() {
            if (!this.docToDelete) return;

            try {
                const response = await fetch(`/api/documents/${this.docToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    await this.loadDocuments();
                    this.showDeleteModal = false;
                    this.docToDelete = null;
                }
            } catch (error) {
                console.error('Error deleting document:', error);
            }
        },

        handleLogout() {
            localStorage.removeItem('dof_user');
            window.location.href = '/login';
        },

        formatDate(isoString) {
            if (!isoString) return { d: '-', t: '-' };
            const date = new Date(isoString);
            const d = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const t = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            return { d, t };
        },

        getStatusClass(status) {
            const classes = {
                draft: 'bg-slate-100 text-slate-600',
                pending_review: 'bg-amber-50 text-amber-600',
                needs_revision: 'bg-red-50 text-red-600',
                approved: 'bg-emerald-50 text-emerald-600',
                received: 'bg-blue-50 text-blue-600'
            };
            return classes[status] || classes.draft;
        },

        getStatusLabel(status) {
            const labels = {
                draft: 'Draft',
                pending_review: 'Review',
                needs_revision: 'Revisi',
                approved: 'Approved',
                received: 'Diterima'
            };
            return labels[status] || 'Draft';
        }
    }
}
