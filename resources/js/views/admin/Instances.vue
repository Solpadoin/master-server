<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Server Instances</h1>
        <p class="text-gray-400 mt-1">Manage your game server instances</p>
      </div>
      <router-link
        to="/admin/instances/create"
        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Create Instance
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="instances.length === 0" class="bg-gray-800 rounded-xl p-12 text-center">
      <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
      </svg>
      <h3 class="text-xl font-medium text-white mb-2">No Server Instances</h3>
      <p class="text-gray-400 mb-6">Get started by creating your first server instance.</p>
      <router-link
        to="/admin/instances/create"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Create Instance
      </router-link>
    </div>

    <!-- Instances Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <router-link
        v-for="instance in instances"
        :key="instance.id"
        :to="`/admin/instances/${instance.id}`"
        class="bg-gray-800 rounded-xl p-6 hover:bg-gray-750 transition-colors border border-gray-700 hover:border-indigo-500"
      >
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-white">{{ instance.name }}</h3>
            <p class="text-gray-400 text-sm">Steam ID: {{ instance.steam_app_id }}</p>
          </div>
          <span
            :class="[
              'px-2 py-1 text-xs font-medium rounded-full',
              instance.is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-600/20 text-gray-400'
            ]"
          >
            {{ instance.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-500">Memory Limit</p>
            <p class="text-white font-medium">{{ instance.memory_limit_mb }} MB</p>
          </div>
          <div>
            <p class="text-gray-500">Created</p>
            <p class="text-white font-medium">{{ formatDate(instance.created_at) }}</p>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-700 flex justify-between items-center">
          <a
            :href="instance.steam_store_url"
            target="_blank"
            @click.stop
            class="text-indigo-400 hover:text-indigo-300 text-sm flex items-center"
          >
            View on Steam
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
          </a>
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../../services/api';

const instances = ref([]);
const loading = ref(true);

const fetchInstances = async () => {
  try {
    const response = await api.get('/admin/instances');
    instances.value = response.data || [];
  } catch (error) {
    console.error('Failed to fetch instances:', error);
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

onMounted(() => {
  fetchInstances();
});
</script>
