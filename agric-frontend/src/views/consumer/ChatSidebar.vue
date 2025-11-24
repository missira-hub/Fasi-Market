<template>
  <div class="w-1/3 border-r bg-white h-screen flex flex-col">
    <!-- Search Bar -->
    <div class="p-4 border-b">
      <input
        v-model="search"
        type="text"
        placeholder="Search or start new chat"
        class="w-full rounded-lg border px-3 py-2 text-sm"
      />
    </div>

    <!-- Chat List -->
    <div class="flex-1 overflow-y-auto">
      <div
        v-for="chat in filteredConversations"
        :key="chat.id"
        @click="$emit('open-chat', chat)"
        class="flex items-center p-4 hover:bg-gray-100 cursor-pointer"
      >
        <img
          :src="chat.chat_avatar"
          alt="avatar"
          class="w-12 h-12 rounded-full object-cover mr-3"
        />

        <div class="flex-1">
          <div class="flex justify-between items-center">
            <h3 class="font-semibold">{{ chat.chat_name }}</h3>
            <span class="text-xs text-gray-500">
              {{ formatTime(chat.latest_message?.created_at) }}
            </span>
          </div>

          <div class="flex justify-between items-center">
            <p class="text-sm text-gray-600 truncate w-4/5">
              {{ chat.latest_message?.message_text || "No messages yet" }}
            </p>

            <!-- Unread Count -->
            <span
              v-if="chat.unread_count > 0"
              class="bg-green-500 text-white text-xs rounded-full px-2 py-0.5"
            >
              {{ chat.unread_count }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  conversations: Array
});

const search = ref("");

const filteredConversations = computed(() =>
  props.conversations.filter(c =>
    c.chat_name.toLowerCase().includes(search.value.toLowerCase())
  )
);

function formatTime(datetime) {
  if (!datetime) return "";
  const date = new Date(datetime);
  return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}
</script>

<style scoped>
::-webkit-scrollbar {
  width: 5px;
}
::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 5px;
}
</style>
