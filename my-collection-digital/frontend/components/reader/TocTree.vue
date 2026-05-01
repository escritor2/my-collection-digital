<script setup lang="ts">
type TocItem = {
  id?: string;
  label: string;
  href?: string;
  cfi?: string;
  subitems?: TocItem[];
};

defineProps<{
  items: TocItem[];
  depth?: number;
}>();

const emit = defineEmits<{
  (e: 'navigate', item: TocItem): void;
}>();
</script>

<template>
  <div class="space-y-1">
    <div v-for="(item, idx) in items" :key="item.id || `${depth || 0}-${idx}`" class="space-y-1">
      <button
        @click="emit('navigate', item)"
        :style="{ paddingLeft: `${((depth || 0) * 12) + 8}px` }"
        class="w-full text-left text-xs py-2 rounded border border-zinc-800 hover:bg-zinc-900 text-zinc-200"
      >
        {{ item.label }}
      </button>

      <TocTree
        v-if="item.subitems?.length"
        :items="item.subitems"
        :depth="(depth || 0) + 1"
        @navigate="emit('navigate', $event)"
      />
    </div>
  </div>
</template>

