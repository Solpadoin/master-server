<template>
  <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Master Server</h1>
        <p class="text-gray-400">Initial Setup Wizard</p>
      </div>

      <!-- Progress Steps -->
      <div class="flex justify-center mb-8">
        <div class="flex items-center space-x-4">
          <div
            v-for="(stepItem, index) in steps"
            :key="index"
            class="flex items-center"
          >
            <div
              :class="[
                'w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium transition-colors',
                currentStep > index
                  ? 'bg-green-500 text-white'
                  : currentStep === index
                  ? 'bg-indigo-500 text-white'
                  : 'bg-gray-700 text-gray-400'
              ]"
            >
              <svg v-if="currentStep > index" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span v-else>{{ index + 1 }}</span>
            </div>
            <div v-if="index < steps.length - 1" class="w-16 h-0.5 mx-2" :class="currentStep > index ? 'bg-green-500' : 'bg-gray-700'"></div>
          </div>
        </div>
      </div>

      <!-- Card Container -->
      <div class="bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
        <!-- Step Content -->
        <div class="p-8">
          <!-- Step 1: Welcome -->
          <div v-if="currentStep === 0">
            <h2 class="text-2xl font-semibold text-white mb-4">Welcome to Master Server</h2>
            <p class="text-gray-300 mb-6">
              Thank you for choosing Master Server for your game server management needs.
              This wizard will guide you through the initial setup process.
            </p>
            <div class="bg-gray-700/50 rounded-lg p-4 mb-6">
              <h3 class="text-white font-medium mb-2">Setup Steps:</h3>
              <ul class="text-gray-300 space-y-2">
                <li class="flex items-center">
                  <span class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 text-xs flex items-center justify-center mr-3">1</span>
                  Create Super Admin Account
                </li>
                <li class="flex items-center">
                  <span class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 text-xs flex items-center justify-center mr-3">2</span>
                  Verify Service Connections
                </li>
                <li class="flex items-center">
                  <span class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 text-xs flex items-center justify-center mr-3">3</span>
                  Login to Dashboard
                </li>
              </ul>
            </div>
          </div>

          <!-- Step 2: Create Admin -->
          <div v-if="currentStep === 1">
            <h2 class="text-2xl font-semibold text-white mb-4">Create Super Admin</h2>
            <p class="text-gray-300 mb-6">
              Create your administrator account to manage the Master Server.
            </p>

            <form @submit.prevent="createAdmin" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">
                  Name <span class="text-gray-500">(optional)</span>
                </label>
                <input
                  v-model="adminForm.name"
                  type="text"
                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="Super Admin"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">
                  Email <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="adminForm.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="admin@example.com"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">
                  Password <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="adminForm.password"
                  type="password"
                  required
                  minlength="8"
                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="Minimum 8 characters"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">
                  Confirm Password <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="adminForm.password_confirmation"
                  type="password"
                  required
                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="Confirm your password"
                />
              </div>

              <div v-if="adminError" class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 text-red-400">
                {{ adminError }}
              </div>

              <div v-if="adminCreated" class="bg-green-500/10 border border-green-500/50 rounded-lg p-4 text-green-400">
                Super admin account created successfully!
              </div>
            </form>
          </div>

          <!-- Step 3: Check Services -->
          <div v-if="currentStep === 2">
            <h2 class="text-2xl font-semibold text-white mb-4">Verify Services</h2>
            <p class="text-gray-300 mb-6">
              Check that all required services are running correctly.
            </p>

            <div class="space-y-4 mb-6">
              <!-- Database Status -->
              <div class="flex items-center justify-between p-4 bg-gray-700/50 rounded-lg">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center mr-4">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-white font-medium">Database (MySQL)</p>
                    <p class="text-gray-400 text-sm">{{ services.database?.message || 'Not checked yet' }}</p>
                  </div>
                </div>
                <div v-if="services.database" :class="services.database.status === 'ok' ? 'text-green-400' : 'text-red-400'">
                  <svg v-if="services.database.status === 'ok'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div v-else class="w-6 h-6 rounded-full bg-gray-600"></div>
              </div>

              <!-- Redis Status -->
              <div class="flex items-center justify-between p-4 bg-gray-700/50 rounded-lg">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center mr-4">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-white font-medium">Redis Cache</p>
                    <p class="text-gray-400 text-sm">{{ services.redis?.message || 'Not checked yet' }}</p>
                  </div>
                </div>
                <div v-if="services.redis" :class="services.redis.status === 'ok' ? 'text-green-400' : 'text-red-400'">
                  <svg v-if="services.redis.status === 'ok'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div v-else class="w-6 h-6 rounded-full bg-gray-600"></div>
              </div>
            </div>

            <button
              @click="checkServices"
              :disabled="checkingServices"
              class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-800 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors"
            >
              <span v-if="checkingServices">Checking Services...</span>
              <span v-else>Check Services</span>
            </button>

            <div v-if="allServicesPassed" class="mt-4 bg-green-500/10 border border-green-500/50 rounded-lg p-4 text-green-400 text-center">
              All services passed successfully. You can continue to the next step.
            </div>
          </div>

          <!-- Step 4: Login -->
          <div v-if="currentStep === 3">
            <h2 class="text-2xl font-semibold text-white mb-4">Login to Dashboard</h2>
            <p class="text-gray-300 mb-6">
              Setup is almost complete! Login with your admin credentials to access the dashboard.
            </p>

            <form @submit.prevent="login" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                <input
                  v-model="loginForm.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="admin@example.com"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                <input
                  v-model="loginForm.password"
                  type="password"
                  required
                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="Your password"
                />
              </div>

              <div v-if="loginError" class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 text-red-400">
                {{ loginError }}
              </div>

              <button
                type="submit"
                :disabled="loggingIn"
                class="w-full py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-800 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors"
              >
                <span v-if="loggingIn">Logging in...</span>
                <span v-else>Login & Complete Setup</span>
              </button>
            </form>
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="px-8 py-4 bg-gray-700/50 flex justify-between">
          <button
            v-if="currentStep > 0 && currentStep < 3"
            @click="prevStep"
            class="px-6 py-2 text-gray-300 hover:text-white transition-colors"
          >
            Back
          </button>
          <div v-else></div>

          <button
            v-if="currentStep === 0"
            @click="nextStep"
            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
          >
            Get Started
          </button>

          <button
            v-if="currentStep === 1 && !adminCreated"
            @click="createAdmin"
            :disabled="creatingAdmin"
            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-800 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors"
          >
            <span v-if="creatingAdmin">Creating...</span>
            <span v-else>Create Admin</span>
          </button>

          <button
            v-if="currentStep === 1 && adminCreated"
            @click="nextStep"
            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
          >
            Continue
          </button>

          <button
            v-if="currentStep === 2 && allServicesPassed"
            @click="nextStep"
            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
          >
            Continue to Login
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const steps = ['Welcome', 'Create Admin', 'Check Services', 'Login'];
const currentStep = ref(0);

// Admin form
const adminForm = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});
const adminError = ref('');
const adminCreated = ref(false);
const creatingAdmin = ref(false);

// Services check
const services = reactive({
  database: null,
  redis: null,
});
const checkingServices = ref(false);
const allServicesPassed = ref(false);

// Login form
const loginForm = reactive({
  email: '',
  password: '',
});
const loginError = ref('');
const loggingIn = ref(false);

const nextStep = () => {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++;
  }
};

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
};

const createAdmin = async () => {
  adminError.value = '';
  creatingAdmin.value = true;

  try {
    const response = await fetch('/api/v1/setup/admin', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(adminForm),
    });

    const data = await response.json();

    if (!response.ok) {
      if (data.errors) {
        adminError.value = Object.values(data.errors).flat().join(' ');
      } else {
        adminError.value = data.message || 'Failed to create admin';
      }
      return;
    }

    adminCreated.value = true;
    // Pre-fill login form with the email
    loginForm.email = adminForm.email;
  } catch (error) {
    adminError.value = 'Network error. Please try again.';
  } finally {
    creatingAdmin.value = false;
  }
};

const checkServices = async () => {
  checkingServices.value = true;
  allServicesPassed.value = false;

  try {
    const response = await fetch('/api/v1/setup/check-services', {
      headers: {
        'Accept': 'application/json',
      },
    });

    const data = await response.json();

    services.database = data.services.database;
    services.redis = data.services.redis;
    allServicesPassed.value = data.all_passed;
  } catch (error) {
    services.database = { status: 'error', message: 'Failed to check' };
    services.redis = { status: 'error', message: 'Failed to check' };
  } finally {
    checkingServices.value = false;
  }
};

const login = async () => {
  loginError.value = '';
  loggingIn.value = true;

  try {
    // First, complete the setup
    const completeResponse = await fetch('/api/v1/setup/complete', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    });

    if (!completeResponse.ok) {
      const completeData = await completeResponse.json();
      loginError.value = completeData.message || 'Failed to complete setup';
      loggingIn.value = false;
      return;
    }

    // Then login
    const loginResponse = await fetch('/api/v1/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(loginForm),
    });

    const loginData = await loginResponse.json();

    if (!loginResponse.ok) {
      loginError.value = loginData.message || 'Invalid credentials';
      loggingIn.value = false;
      return;
    }

    // Store token
    localStorage.setItem('auth_token', loginData.token);

    // Redirect to dashboard
    window.location.href = '/admin';
  } catch (error) {
    loginError.value = 'Network error. Please try again.';
  } finally {
    loggingIn.value = false;
  }
};
</script>
