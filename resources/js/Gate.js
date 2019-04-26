export default class Gate {
    constructor(user) {
        this.user = user;
    }

    isSadmin() {
        return this.user.type === 'super admin';
    }

    isAdmin() {
        return this.user.type === 'admin';
    }

    isUser() {
        return this.user.type === 'user';
    }

    isSadminOrAdmin() {
        if (this.user.type === 'super admin' || this.user.type === 'admin') {
            return true;
        }
    }
}