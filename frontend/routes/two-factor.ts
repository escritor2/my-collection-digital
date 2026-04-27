export const qrCode = {
    url: () => '/api/user/two-factor-qr-code'
};

export const recoveryCodes = {
    url: () => '/api/user/two-factor-recovery-codes'
};

export const secretKey = {
    url: () => '/api/user/two-factor-secret-key'
};

export const regenerateRecoveryCodes = {
    form: () => ({ action: '/api/user/two-factor-recovery-codes' })
};

export const confirm = {
    form: () => ({ action: '/user/confirmed-two-factor-authentication' })
};
