<template>
  <div class="max-w-2xl mx-auto">
    <!-- Back Button -->
    <router-link
      to="/admin/instances"
      class="inline-flex items-center text-gray-400 hover:text-white mb-6 transition-colors"
    >
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
      Back to Instances
    </router-link>

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-white">Create Server Instance</h1>
      <p class="text-gray-400 mt-1">Set up a new game server instance</p>
    </div>

    <!-- Form Card -->
    <div class="bg-gray-800 rounded-xl p-8">
      <form @submit.prevent="createInstance" class="space-y-6">
        <!-- Steam App ID -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Steam App ID <span class="text-red-400">*</span>
          </label>
          <input
            v-model="form.steam_app_id"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            required
            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            placeholder="e.g., 2965940"
            @input="validateSteamAppId"
          />
          <div class="mt-2 p-3 bg-gray-700/50 rounded-lg">
            <p class="text-sm text-gray-400">
              <span class="text-indigo-400 font-medium">How to find your Steam App ID:</span>
            </p>
            <p class="text-sm text-gray-400 mt-1">
              Go to your game's Steam store page. The App ID is the number in the URL.
            </p>
            <p class="text-sm text-gray-400 mt-2">
              Example:
              <code class="bg-gray-800 px-2 py-1 rounded text-indigo-300">
                https://store.steampowered.com/app/<span class="text-yellow-400 font-bold">2965940</span>/Soviet_Anomaly_7/
              </code>
            </p>
            <p class="text-sm text-gray-400 mt-1">
              The highlighted number (<span class="text-yellow-400 font-bold">2965940</span>) is your Steam App ID.
            </p>
          </div>
          <p v-if="errors.steam_app_id" class="mt-2 text-sm text-red-400">{{ errors.steam_app_id }}</p>
        </div>

        <!-- Game Name -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Game Name <span class="text-red-400">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            minlength="2"
            maxlength="255"
            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            placeholder="e.g., Soviet Anomaly 7"
          />
          <p class="mt-1 text-sm text-gray-500">A display name for this game instance</p>
          <p v-if="errors.name" class="mt-2 text-sm text-red-400">{{ errors.name }}</p>
        </div>

        <!-- Memory Limit -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Redis Memory Limit
          </label>
          <div class="flex items-center space-x-4">
            <input
              v-model.number="form.memory_limit_mb"
              type="range"
              min="1"
              max="10"
              class="flex-1 h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-indigo-500"
            />
            <div class="w-20 px-3 py-2 bg-gray-700 rounded-lg text-center">
              <span class="text-white font-medium">{{ form.memory_limit_mb }}</span>
              <span class="text-gray-400 text-sm"> MB</span>
            </div>
          </div>
          <p class="mt-2 text-sm text-gray-500">
            Maximum Redis cache memory for this instance (1-10 MB).
            Used to store active server data and statistics.
          </p>
          <p v-if="errors.memory_limit_mb" class="mt-2 text-sm text-red-400">{{ errors.memory_limit_mb }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 text-red-400">
          {{ error }}
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4 pt-4">
          <router-link
            to="/admin/instances"
            class="px-6 py-3 text-gray-300 hover:text-white transition-colors"
          >
            Cancel
          </router-link>
          <button
            type="submit"
            :disabled="submitting"
            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-800 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center"
          >
            <span v-if="submitting" class="flex items-center">
              <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating...
            </span>
            <span v-else>Create Instance</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../../services/api';

const router = useRouter();

const form = reactive({
  steam_app_id: '',
  name: '',
  memory_limit_mb: 1,
});

const errors = reactive({
  steam_app_id: '',
  name: '',
  memory_limit_mb: '',
});

const error = ref('');
const submitting = ref(false);

const validateSteamAppId = () => {
  // Remove non-numeric characters
  form.steam_app_id = form.steam_app_id.replace(/\D/g, '');

  if (form.steam_app_id && !/^\d+$/.test(form.steam_app_id)) {
    errors.steam_app_id = 'Steam App ID must be a number';
  } else {
    errors.steam_app_id = '';
  }
};

const createInstance = async () => {
  // Reset errors
  error.value = '';
  Object.keys(errors).forEach(key => errors[key] = '');

  // Validate
  if (!form.steam_app_id) {
    errors.steam_app_id = 'Steam App ID is required';
    return;
  }

  if (!/^\d+$/.test(form.steam_app_id)) {
    errors.steam_app_id = 'Steam App ID must be a number';
    return;
  }

  if (!form.name || form.name.length < 2) {
    errors.name = 'Game name must be at least 2 characters';
    return;
  }

  submitting.value = true;

  try {
    const response = await api.post('/admin/instances', {
      steam_app_id: parseInt(form.steam_app_id, 10),
      name: form.name,
      memory_limit_mb: form.memory_limit_mb,
    });

    // Redirect to the new instance dashboard
    router.push(`/admin/instances/${response.data.id}`);
  } catch (err) {
    if (err.response?.data?.errors) {
      const serverErrors = err.response.data.errors;
      Object.keys(serverErrors).forEach(key => {
        if (errors[key] !== undefined) {
          errors[key] = serverErrors[key][0];
        }
      });
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message;
    } else {
      error.value = 'Failed to create instance. Please try again.';
    }
  } finally {
    submitting.value = false;
  }
};
</script>
