window.adminApp = function() {
    return {
        activeTab: 'users',
        users: [],
        groups: [],
        showUserModal: false,
        showGroupModal: false,
        editingUser: null,
        userForm: {},
        groupForm: {},
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
