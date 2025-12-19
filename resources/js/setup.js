import { createApp } from 'vue';
import SetupWizard from './components/setup/SetupWizard.vue';
import '../css/app.css';

const app = createApp(SetupWizard);
app.mount('#setup-app');
