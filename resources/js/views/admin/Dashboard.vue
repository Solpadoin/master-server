<template>
  <div class="min-h-screen bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-gray-800 border-b border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <router-link to="/admin" class="text-white font-bold text-xl">
              Master Server
            </router-link>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              to="/admin"
              class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-white bg-gray-700': $route.name === 'admin.home' }"
            >
              Dashboard
            </router-link>
            <router-link
              to="/admin/games"
              class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-white bg-gray-700': $route.name === 'admin.games' }"
            >
              Games
            </router-link>
            <router-link
              to="/admin/instances"
              class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-white bg-gray-700': $route.name === 'admin.instances' }"
            >
              Instances
            </router-link>

            <!-- User Menu -->
            <div class="relative ml-4">
              <button
                @click="showUserMenu = !showUserMenu"
                class="flex items-center text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              >
                <span v-if="user">{{ user.name }}</span>
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <!-- Dropdown -->
              <div
                v-if="showUserMenu"
                class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg py-1 z-50 border border-gray-700"
              >
                <div class="px-4 py-2 border-b border-gray-700">
                  <p class="text-sm text-white">{{ user?.name }}</p>
                  <p class="text-xs text-gray-400">{{ user?.email }}</p>
                </div>
                <button
                  @click="handleLogout"
                  class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white"
                >
                  Sign out
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { auth } from '../../services/api';

const router = useRouter();
const user = ref(null);
const showUserMenu = ref(false);

const fetchUser = async () => {
  try {
    const data = await auth.getUser();
    user.value = data.user;
  } catch (error) {
    // If unauthorized, redirect to login
    router.push({ name: 'login' });
  }
};

const handleLogout = async () => {
  try {
    await auth.logout();
  } finally {
    router.push({ name: 'login' });
  }
};

// Close menu when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showUserMenu.value = false;
  }
};

onMounted(() => {
  fetchUser();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
