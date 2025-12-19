<template>
  <div>
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

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-500/10 border border-red-500/50 rounded-lg p-6 text-center">
      <p class="text-red-400">{{ error }}</p>
      <router-link to="/admin/instances" class="mt-4 inline-block text-indigo-400 hover:text-indigo-300">
        Return to Instances
      </router-link>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="instance">
      <!-- Header -->
      <div class="flex justify-between items-start mb-8">
        <div>
          <div class="flex items-center space-x-3">
            <h1 class="text-2xl font-bold text-white">{{ instance.name }}</h1>
            <span
              :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                instance.is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-600/20 text-gray-400'
              ]"
            >
              {{ instance.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <p class="text-gray-400 mt-1">Steam App ID: {{ instance.steam_app_id }}</p>
        </div>
        <div class="flex items-center space-x-3">
          <a
            :href="instance.steam_store_url"
            target="_blank"
            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors flex items-center"
          >
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
              <path d="M11.979 0C5.678 0 .511 4.86.022 11.037l6.432 2.658c.545-.371 1.203-.59 1.912-.59.063 0 .125.004.188.006l2.861-4.142V8.91c0-2.495 2.028-4.524 4.524-4.524 2.494 0 4.524 2.031 4.524 4.527s-2.03 4.525-4.524 4.525h-.105l-4.076 2.911c0 .052.004.105.004.159 0 1.875-1.515 3.396-3.39 3.396-1.635 0-3.016-1.173-3.331-2.727L.436 15.27C1.862 20.307 6.486 24 11.979 24c6.627 0 11.999-5.373 11.999-12S18.605 0 11.979 0zM7.54 18.21l-1.473-.61c.262.543.714.999 1.314 1.25 1.297.539 2.793-.076 3.332-1.375.263-.63.264-1.319.005-1.949s-.75-1.121-1.377-1.383c-.624-.26-1.29-.249-1.878-.03l1.523.63c.956.4 1.409 1.5 1.009 2.455-.397.957-1.497 1.41-2.454 1.012zm11.415-9.303c0-1.662-1.353-3.015-3.015-3.015-1.665 0-3.015 1.353-3.015 3.015 0 1.665 1.35 3.015 3.015 3.015 1.663 0 3.015-1.35 3.015-3.015zm-5.273-.005c0-1.252 1.013-2.266 2.265-2.266 1.249 0 2.266 1.014 2.266 2.266 0 1.251-1.017 2.265-2.266 2.265-1.253 0-2.265-1.014-2.265-2.265z"/>
            </svg>
            View on Steam
          </a>
          <button
            @click="deleteInstance"
            class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg transition-colors"
          >
            Delete
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gray-800 rounded-xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm">Active Servers</p>
              <p class="text-3xl font-bold text-white mt-1">{{ stats.active_servers }}</p>
            </div>
            <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm">Current Players</p>
              <p class="text-3xl font-bold text-white mt-1">{{ stats.current_players }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm">Peak Players (24h)</p>
              <p class="text-3xl font-bold text-white mt-1">{{ stats.peak_players }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Servers Chart -->
        <div class="bg-gray-800 rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4">Active Servers (24h)</h3>
          <div class="h-64">
            <Line :data="activeServersChartData" :options="chartOptions" />
          </div>
        </div>

        <!-- Peak Players Chart -->
        <div class="bg-gray-800 rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4">Peak Players (24h)</h3>
          <div class="h-64">
            <Line :data="peakPlayersChartData" :options="chartOptions" />
          </div>
        </div>
      </div>

      <!-- Instance Details -->
      <div class="bg-gray-800 rounded-xl p-6 mt-6">
        <h3 class="text-lg font-semibold text-white mb-4">Instance Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
          <div>
            <p class="text-gray-500">Memory Used</p>
            <div class="flex items-center mt-1">
              <p class="text-white font-medium">{{ memory.used_formatted }}</p>
              <span class="text-gray-500 mx-1">/</span>
              <p class="text-gray-400">{{ memory.limit_formatted }}</p>
            </div>
            <div class="mt-2 w-full bg-gray-700 rounded-full h-2">
              <div
                class="h-2 rounded-full transition-all"
                :class="getMemoryBarColor(memory.usage_percent)"
                :style="{ width: `${Math.min(memory.usage_percent, 100)}%` }"
              ></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ memory.usage_percent }}% used</p>
          </div>
          <div>
            <p class="text-gray-500">Memory Limit</p>
            <p class="text-white font-medium">{{ instance.memory_limit_mb }} MB</p>
          </div>
          <div>
            <p class="text-gray-500">Steam App ID</p>
            <p class="text-white font-medium">{{ instance.steam_app_id }}</p>
          </div>
          <div>
            <p class="text-gray-500">Created</p>
            <p class="text-white font-medium">{{ formatDate(instance.created_at) }}</p>
          </div>
          <div>
            <p class="text-gray-500">Last Updated</p>
            <p class="text-white font-medium">{{ formatDate(instance.updated_at) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../../services/api';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';
import { Line } from 'vue-chartjs';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const error = ref('');
const instance = ref(null);
const stats = reactive({
  active_servers: 0,
  current_players: 0,
  peak_players: 0,
});
const memory = reactive({
  used_bytes: 0,
  used_formatted: '0 B',
  limit_bytes: 0,
  limit_formatted: '0 MB',
  usage_percent: 0,
});
const hourlyStats = ref([]);

let refreshInterval = null;

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    x: {
      grid: {
        color: 'rgba(75, 85, 99, 0.3)',
      },
      ticks: {
        color: '#9CA3AF',
      },
    },
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(75, 85, 99, 0.3)',
      },
      ticks: {
        color: '#9CA3AF',
        stepSize: 1,
      },
    },
  },
};

const activeServersChartData = computed(() => ({
  labels: hourlyStats.value.map(s => s.hour),
  datasets: [{
    label: 'Active Servers',
    data: hourlyStats.value.map(s => s.active_servers),
    borderColor: '#10B981',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    fill: true,
    tension: 0.4,
  }],
}));

const peakPlayersChartData = computed(() => ({
  labels: hourlyStats.value.map(s => s.hour),
  datasets: [{
    label: 'Peak Players',
    data: hourlyStats.value.map(s => s.peak_players),
    borderColor: '#F59E0B',
    backgroundColor: 'rgba(245, 158, 11, 0.1)',
    fill: true,
    tension: 0.4,
  }],
}));

const fetchStats = async () => {
  try {
    const response = await api.get(`/admin/instances/${route.params.id}/stats`);
    instance.value = response.data.instance;
    stats.active_servers = response.data.current.active_servers;
    stats.current_players = response.data.current.current_players;
    stats.peak_players = response.data.current.peak_players;
    hourlyStats.value = response.data.hourly;

    // Update memory stats
    if (response.data.memory) {
      memory.used_bytes = response.data.memory.used_bytes;
      memory.used_formatted = response.data.memory.used_formatted;
      memory.limit_bytes = response.data.memory.limit_bytes;
      memory.limit_formatted = response.data.memory.limit_formatted;
      memory.usage_percent = response.data.memory.usage_percent;
    }
  } catch (err) {
    if (err.response?.status === 404) {
      error.value = 'Instance not found.';
    } else {
      error.value = 'Failed to load instance data.';
    }
  } finally {
    loading.value = false;
  }
};

const getMemoryBarColor = (percent) => {
  if (percent >= 90) return 'bg-red-500';
  if (percent >= 70) return 'bg-yellow-500';
  return 'bg-green-500';
};

const deleteInstance = async () => {
  if (!confirm(`Are you sure you want to delete "${instance.value.name}"? This action cannot be undone.`)) {
    return;
  }

  try {
    await api.delete(`/admin/instances/${route.params.id}`);
    router.push('/admin/instances');
  } catch (err) {
    alert('Failed to delete instance. Please try again.');
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

onMounted(() => {
  fetchStats();
  // Refresh stats every 60 seconds
  refreshInterval = setInterval(fetchStats, 60000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>
