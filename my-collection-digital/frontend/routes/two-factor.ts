export const twoFactor = {
  href: '/settings/security',
};

const build = (path: string) => ({
  url: () => path,
});

export const qrCode = build('/user/two-factor-qr-code');
export const secretKey = build('/user/two-factor-secret-key');
export const recoveryCodes = build('/user/two-factor-recovery-codes');
export const confirm = build('/user/confirmed-two-factor-authentication');
export const regenerateRecoveryCodes = build('/user/two-factor-recovery-codes');

