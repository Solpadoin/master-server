<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Monitoring</h1>
        <p class="text-gray-400 mt-1">Real-time server status (last 5 minutes)</p>
      </div>
      <button
        @click="fetchMonitoring"
        :disabled="loading"
        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 disabled:bg-gray-800 text-white rounded-lg transition-colors flex items-center"
      >
        <svg
          class="w-5 h-5 mr-2"
          :class="{ 'animate-spin': loading }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Refresh
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && instances.length === 0" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="instances.length === 0" class="bg-gray-800 rounded-xl p-12 text-center">
      <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
      </svg>
      <h3 class="text-xl font-medium text-white mb-2">No Instances</h3>
      <p class="text-gray-400">Create an instance to start monitoring servers.</p>
    </div>

    <!-- Steam-styled Table -->
    <div v-else class="space-y-4">
      <!-- Table Header -->
      <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-t-xl border border-gray-700 overflow-hidden">
        <div class="grid grid-cols-12 gap-4 px-6 py-3 text-sm font-medium text-gray-400 uppercase tracking-wider border-b border-gray-700">
          <div class="col-span-1">ID</div>
          <div class="col-span-3">Instance</div>
          <div class="col-span-2">Steam App ID</div>
          <div class="col-span-2 text-center">Servers</div>
          <div class="col-span-2 text-center">Players</div>
          <div class="col-span-2 text-center">Status</div>
        </div>
      </div>

      <!-- Instance Rows -->
      <div class="space-y-2">
        <div
          v-for="instance in instances"
          :key="instance.id"
          class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden transition-all duration-200"
          :class="{ 'ring-2 ring-indigo-500/50': expandedInstance === instance.id }"
        >
          <!-- Main Row -->
          <div
            @click="toggleExpand(instance.id)"
            class="grid grid-cols-12 gap-4 px-6 py-4 cursor-pointer hover:bg-gray-750 transition-colors items-center"
          >
            <div class="col-span-1">
              <span class="text-gray-500 font-mono">#{{ instance.id }}</span>
            </div>
            <div class="col-span-3">
              <p class="text-white font-medium">{{ instance.name }}</p>
            </div>
            <div class="col-span-2">
              <a
                :href="`https://store.steampowered.com/app/${instance.steam_app_id}`"
                target="_blank"
                @click.stop
                class="text-indigo-400 hover:text-indigo-300 font-mono"
              >
                {{ instance.steam_app_id }}
              </a>
            </div>
            <div class="col-span-2 text-center">
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="instance.server_count > 0 ? 'bg-green-500/20 text-green-400' : 'bg-gray-600/20 text-gray-400'"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
                {{ instance.server_count }}
              </span>
            </div>
            <div class="col-span-2 text-center">
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="instance.total_players > 0 ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-600/20 text-gray-400'"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{ instance.total_players }}
              </span>
            </div>
            <div class="col-span-2 flex items-center justify-center">
              <span
                class="px-2 py-1 text-xs font-medium rounded-full mr-2"
                :class="instance.is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
              >
                {{ instance.is_active ? 'Active' : 'Inactive' }}
              </span>
              <svg
                class="w-5 h-5 text-gray-400 transition-transform duration-200"
                :class="{ 'rotate-180': expandedInstance === instance.id }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </div>
          </div>

          <!-- Expanded Server Details -->
          <div
            v-if="expandedInstance === instance.id"
            class="border-t border-gray-700 bg-gray-900/50"
          >
            <div v-if="instance.servers.length === 0" class="px-6 py-8 text-center">
              <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
              </svg>
              <p class="text-gray-400">No active servers in the last 5 minutes</p>
            </div>

            <div v-else class="p-4">
              <!-- Server Table Header -->
              <div class="grid grid-cols-12 gap-2 px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="col-span-3">Server Name</div>
                <div class="col-span-2">Map</div>
                <div class="col-span-2">Players</div>
                <div class="col-span-2">Region</div>
                <div class="col-span-2">Address</div>
                <div class="col-span-1">Heartbeat</div>
              </div>

              <!-- Server Rows -->
              <div class="space-y-1">
                <div
                  v-for="server in instance.servers"
                  :key="server.server_id"
                  class="grid grid-cols-12 gap-2 px-4 py-3 bg-gray-800/50 rounded-lg text-sm items-center hover:bg-gray-800 transition-colors"
                >
                  <div class="col-span-3">
                    <p class="text-white font-medium truncate" :title="server.server_name">
                      {{ server.server_name }}
                    </p>
                  </div>
                  <div class="col-span-2">
                    <span class="text-gray-300 font-mono text-xs">{{ server.map }}</span>
                  </div>
                  <div class="col-span-2">
                    <div class="flex items-center">
                      <div class="w-16 bg-gray-700 rounded-full h-2 mr-2">
                        <div
                          class="h-2 rounded-full transition-all"
                          :class="getPlayerBarColor(server.player_count, server.max_players)"
                          :style="{ width: `${(server.player_count / server.max_players) * 100}%` }"
                        ></div>
                      </div>
                      <span class="text-gray-300 text-xs">
                        {{ server.player_count }}/{{ server.max_players }}
                      </span>
                    </div>
                  </div>
                  <div class="col-span-2">
                    <span class="px-2 py-1 bg-gray-700 rounded text-xs text-gray-300">
                      {{ server.region }}
                    </span>
                  </div>
                  <div class="col-span-2">
                    <span class="text-gray-400 font-mono text-xs">
                      {{ server.ip }}:{{ server.port }}
                    </span>
                  </div>
                  <div class="col-span-1">
                    <span
                      class="text-xs"
                      :class="server.last_heartbeat_ago < 60 ? 'text-green-400' : 'text-yellow-400'"
                    >
                      {{ formatHeartbeat(server.last_heartbeat_ago) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Last Updated -->
      <div v-if="meta.generated_at" class="text-center text-sm text-gray-500 mt-4">
        Last updated: {{ formatDate(meta.generated_at) }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { api } from '../../services/api';

const loading = ref(true);
const instances = ref([]);
const expandedInstance = ref(null);
const meta = reactive({
  ttl_seconds: 300,
  generated_at: null,
});

let refreshInterval = null;

const fetchMonitoring = async () => {
  loading.value = true;
  try {
    const response = await api.get('/admin/monitoring');
    instances.value = response.data;
    if (response.meta) {
      meta.ttl_seconds = response.meta.ttl_seconds;
      meta.generated_at = response.meta.generated_at;
    }
  } catch (error) {
    console.error('Failed to fetch monitoring data:', error);
  } finally {
    loading.value = false;
  }
};

const toggleExpand = (instanceId) => {
  expandedInstance.value = expandedInstance.value === instanceId ? null : instanceId;
};

const getPlayerBarColor = (current, max) => {
  const ratio = current / max;
  if (ratio >= 0.9) return 'bg-red-500';
  if (ratio >= 0.7) return 'bg-yellow-500';
  if (ratio >= 0.3) return 'bg-green-500';
  return 'bg-blue-500';
};

const formatHeartbeat = (seconds) => {
  if (seconds < 60) return `${seconds}s`;
  const minutes = Math.floor(seconds / 60);
  return `${minutes}m`;
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};

onMounted(() => {
  fetchMonitoring();
  // Auto-refresh every 30 seconds
  refreshInterval = setInterval(fetchMonitoring, 30000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>
