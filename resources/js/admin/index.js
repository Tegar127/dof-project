window.adminApp = function() {
    return {
        activeTab: 'users',
        users: [],
        groups: [],
        showUserModal: false,
        showGroupModal: false,
        
        // Group View State (Master-Detail)
        groupViewMode: 'list', // 'list' or 'details'
        selectedGroup: null, // Stores currently viewed group details
        loadingGroupDetails: false,
        
        // Document History State
        viewingDocumentHistory: null,
        viewingDocumentHistoryTitle: '',
        documentVersions: [],
        loadingVersions: false,

        // Delete State
        showDeleteModal: false,
        docToDelete: null,
        deleteReason: '',

        editingUser: null,
        userForm: {},
        groupForm: { invited_users: [], is_private: false },
        token: null,
        notification: {
            show: false,
            message: '',
            type: 'success'
        },

        async init() {
            const userData = localStorage.getItem('dof_user');
            this.token = localStorage.getItem('dof_token');

            if (!userData || !this.token) {
                window.location.href = '/login';
                return;
            }

            const currentUser = JSON.parse(userData);
            if (currentUser.role !== 'admin') {
                window.location.href = '/dashboard';  
                return;
            }

            await this.loadUsers();
            await this.loadGroups();
        },

        showNotification(message, type = 'success') {
            this.notification.message = message;
            this.notification.type = type;
            this.notification.show = true;
            setTimeout(() => {
                this.notification.show = false;
            }, 3000);
        },

        openDeleteModal(docId, docTitle) {
            console.log('Opening delete modal for:', docId, docTitle);
            this.docToDelete = { id: docId, title: docTitle };
            this.deleteReason = '';
            this.showDeleteModal = true;
        },

        async confirmDelete() {
            if (!this.docToDelete) {
                console.error('No document selected to delete');
                return;
            }

            console.log('Confirming delete for document ID:', this.docToDelete.id);

            try {
                const response = await fetch(`/api/documents/${this.docToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason: this.deleteReason })
                });

                if (response.ok) {
                    this.showNotification('Document deleted successfully');
                    this.showDeleteModal = false;
                    
                    const deletedId = this.docToDelete.id;
                    this.docToDelete = null;

                    // Refresh current view
                    if (this.selectedGroup) {
                        await this.loadGroupDetails(this.selectedGroup.group.id);
                    }
                } else {
                    const data = await response.json();
                    console.error('Delete failed:', data);
                    this.showNotification(data.message || 'Error deleting document', 'error');
                }
            } catch (error) {
                console.error('Error during document deletion:', error);
                this.showNotification('Error deleting document', 'error');
            }
        },

        async loadGroupDetails(groupId) {
            this.loadingGroupDetails = true;
            this.groupViewMode = 'details'; // Switch to details view
            this.selectedGroup = null; // Clear previous
            this.closeDocumentHistory(); // Reset history view
            
            try {
                const response = await fetch(`/api/groups/${groupId}/stats`, {
                    headers: { 
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json' 
                    }
                });
                if (response.ok) {
                    this.selectedGroup = await response.json();
                } else {
                    this.showNotification('Error loading group details', 'error');
                    this.groupViewMode = 'list'; // Revert on error
                }
            } catch (error) {
                console.error('Error loading group details:', error);
                this.showNotification('Error loading group details', 'error');
                this.groupViewMode = 'list'; // Revert on error
            } finally {
                this.loadingGroupDetails = false;
            }
        },

        closeGroupDetails() {
            this.groupViewMode = 'list';
            this.selectedGroup = null;
            this.closeDocumentHistory();
        },

        async loadDocumentVersions(docId, docTitle) {
            this.viewingDocumentHistory = docId;
            this.viewingDocumentHistoryTitle = docTitle;
            this.loadingVersions = true;
            this.documentVersions = [];

            try {
                const response = await fetch(`/api/documents/${docId}/versions`, {
                    headers: { 
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json' 
                    }
                });
                if (response.ok) {
                    this.documentVersions = await response.json();
                } else {
                    this.showNotification('Error loading document versions', 'error');
                }
            } catch (error) {
                console.error('Error loading versions:', error);
                this.showNotification('Error loading versions', 'error');
            } finally {
                this.loadingVersions = false;
            }
        },

        closeDocumentHistory() {
            this.viewingDocumentHistory = null;
            this.viewingDocumentHistoryTitle = '';
            this.documentVersions = [];
        },

        formatDate(isoString) {
            if (!isoString) return '-';
            const date = new Date(isoString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        },

        async loadUsers() {
            try {
                const response = await fetch('/api/users', {
                    headers: { 
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json' 
                    }
                });
                if (response.ok) {
                    this.users = await response.json();
                }
            } catch (error) {
                console.error('Error loading users:', error);
                this.showNotification('Error loading users', 'error');
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
                if (response.ok) {
                    this.groups = await response.json();
                }
            } catch (error) {
                console.error('Error loading groups:', error);
                this.showNotification('Error loading groups', 'error');
            }
        },

        async saveUser() {
            try {
                const response = await fetch('/api/users', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.userForm)
                });

                if (response.ok) {
                    await this.loadUsers();
                    this.showUserModal = false;
                    this.userForm = {};
                    this.showNotification('User saved successfully');
                } else {
                    const data = await response.json();
                    this.showNotification(data.message || 'Error saving user', 'error');
                }
            } catch (error) {
                console.error('Error saving user:', error);
                this.showNotification('An unexpected error occurred.', 'error');
            }
        },

        async deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user?')) return;

            try {
                const response = await fetch(`/api/users/${userId}`, {
                    method: 'DELETE',
                    headers: { 
                        'Authorization': 'Bearer ' + this.token,
                        'Accept': 'application/json' 
                    }
                });

                if (response.ok) {
                    await this.loadUsers();
                    alert('User deleted successfully');
                } else {
                    this.showNotification('Error deleting user', 'error');
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                this.showNotification('Error deleting user', 'error');
            }
        },

        async saveGroup() {
            try {
                const response = await fetch('/api/groups', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + this.token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.groupForm)
                });

                if (response.ok) {
                    await this.loadGroups();
                    this.showGroupModal = false;
                    this.groupForm = {};
                    this.showNotification('Group saved successfully');
                } else {
                    const data = await response.json();
                    this.showNotification(data.message || 'Error saving group', 'error');
                }
            } catch (error) {
                console.error('Error saving group:', error);
                this.showNotification('An unexpected error occurred.', 'error');
            }
        },

        getRoleBadge(role) {
            const badges = {
                admin: 'bg-purple-100 text-purple-800',
                user: 'bg-blue-100 text-blue-800',
                reviewer: 'bg-green-100 text-green-800'
            };
            return badges[role] || 'bg-gray-100 text-gray-800';
        },

        handleLogout() {
            localStorage.removeItem('dof_user');
            localStorage.removeItem('dof_token');
            window.location.href = '/login';
        }
    }
}
