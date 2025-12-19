<template>
  <div>
    <h1 class="text-2xl font-bold text-white mb-6">Dashboard</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="bg-gray-800 rounded-xl p-6">
        <div class="flex items-center">
          <div class="w-12 h-12 rounded-lg bg-indigo-500/20 flex items-center justify-center mr-4">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
            </svg>
          </div>
          <div>
            <p class="text-gray-400 text-sm">Active Servers</p>
            <p class="text-2xl font-bold text-white">{{ loading ? '...' : stats.active_servers }}</p>
          </div>
        </div>
      </div>

      <div class="bg-gray-800 rounded-xl p-6">
        <div class="flex items-center">
          <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center mr-4">
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
          </div>
          <div>
            <p class="text-gray-400 text-sm">Instances</p>
            <p class="text-2xl font-bold text-white">{{ loading ? '...' : stats.instances }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gray-800 rounded-xl p-6">
      <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <router-link
          to="/admin/instances/create"
          class="flex items-center p-4 bg-gray-700/50 hover:bg-gray-700 rounded-lg transition-colors"
        >
          <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
          </div>
          <div>
            <p class="text-white font-medium">Create Instance</p>
            <p class="text-gray-400 text-sm">Add a new game server instance</p>
          </div>
        </router-link>

        <router-link
          to="/admin/instances"
          class="flex items-center p-4 bg-gray-700/50 hover:bg-gray-700 rounded-lg transition-colors"
        >
          <div class="w-10 h-10 rounded-lg bg-indigo-500/20 flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
          </div>
          <div>
            <p class="text-white font-medium">View Instances</p>
            <p class="text-gray-400 text-sm">Manage existing instances</p>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Welcome Message -->
    <div class="mt-6 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 rounded-xl p-6">
      <h2 class="text-lg font-semibold text-white mb-2">Welcome to Master Server</h2>
      <p class="text-gray-300">
        Your master server is ready to manage game servers. Create instances for your games,
        and game servers can then register and send heartbeats to keep their status updated.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '../../services/api';

const loading = ref(true);
const stats = reactive({
  active_servers: 0,
  instances: 0,
});

const fetchDashboard = async () => {
  try {
    const response = await api.get('/admin/dashboard');
    stats.active_servers = response.data.active_servers;
    stats.instances = response.data.instances;
  } catch (error) {
    console.error('Failed to fetch dashboard:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboard();
});
</script>
