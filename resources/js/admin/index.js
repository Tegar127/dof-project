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

        async init() {
            const userData = localStorage.getItem('dof_user');
            if (!userData) {
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

        async loadUsers() {
            try {
                const response = await fetch('/api/users', {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    this.users = await response.json();
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        },

        async loadGroups() {
            try {
                const response = await fetch('/api/groups', {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    this.groups = await response.json();
                }
            } catch (error) {
                console.error('Error loading groups:', error);
            }
        },

        async saveUser() {
            try {
                const response = await fetch('/api/users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.userForm)
                });

                if (response.ok) {
                    await this.loadUsers();
                    this.showUserModal = false;
                    this.userForm = {};
                }
            } catch (error) {
                console.error('Error saving user:', error);
            }
        },

        async deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user?')) return;

            try {
                const response = await fetch(`/api/users/${userId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    await this.loadUsers();
                }
            } catch (error) {
                console.error('Error deleting user:', error);
            }
        },

        async saveGroup() {
            try {
                const response = await fetch('/api/groups', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.groupForm)
                });

                if (response.ok) {
                    await this.loadGroups();
                    this.showGroupModal = false;
                    this.groupForm = {};
                }
            } catch (error) {
                console.error('Error saving group:', error);
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
            window.location.href = '/login';
        }
    }
}
