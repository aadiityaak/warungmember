import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.warungmember.app',
  appName: 'WarungMember',
  webDir: 'capacitor-www',
  server: {
    // Ganti dengan URL production Laravel app setelah deploy
    url: 'https://your-app.com',
    cleartext: true,
  },
  plugins: {
    PushNotifications: {
      presentationOptions: ['badge', 'sound', 'alert'],
    },
  },
};

export default config;
