import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.warungmember.app',
  appName: 'Mas Mbull',
  webDir: 'capacitor-www',
  server: {
    url: 'https://prototype8.sweet.web.id',
    cleartext: true,
  },
  plugins: {
    PushNotifications: {
      presentationOptions: ['badge', 'sound', 'alert'],
    },
    LocalNotifications: {
      smallIcon: 'ic_stat_icon',
      iconColor: '#488AFF',
    },
  },
};

export default config;
