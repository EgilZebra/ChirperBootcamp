<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Chirp from '@/Components/Chirp.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps(['chirps', 'alternatives']);


const selectedAlternative = ref('');
const isExpanded = ref(false);
const alternativesREF = ref([]);

watch(() => props.alternatives, (newValue) => {
  alternativesREF.value = newValue;
})

const selectAlternative = (alternative) => {
  selectedAlternative.value = alternative;
  form.message = alternative;  
};
 
const handleGeneratedSubmit = () => {
  form.post(route('chirps.store'), { 
    onSuccess: () => {
      form.reset();
      alternativesREF.value = [];
      isExpanded.value = false;
    }
  });
}

const form = useForm({
  topic: '',
  mood: '',
  seriousnessLevel: 1,
  message: ''             
});

</script>

<template>
    <Head title="Chirps" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('chirps.store'), { onSuccess: () => form.reset() })">
                <textarea
                    v-model="form.message"
                    placeholder="What's on your mind?"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                ></textarea>
                <InputError :message="form.errors.message" class="mt-2" />
                <PrimaryButton class="mt-4">Chirp</PrimaryButton>

                
<div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
        <PrimaryButton @click="isExpanded = !isExpanded">
          {{ isExpanded ? 'Collapse' : 'Expand' }} AI Options
        </PrimaryButton>

        <div v-if="isExpanded" class="mt-4">
          <form @submit.prevent="form.post(route('chirps.generate'))">
            <div class="mb-4">
              <label for="topic" class="block text-sm font-medium text-gray-700">Topic</label>
              <input
                id="topic"
                type="text"
                v-model="form.topic"
                placeholder="What is your topic?"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              />
            </div>

            <div class="mb-4">
              <label for="mood" class="block text-sm font-medium text-gray-700">Mood</label>
              <input
                id="mood"
                type="text"
                v-model="form.mood"
                placeholder="What is your mood?"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              />
            </div>

            <div class="mb-4">
              <label for="seriousnessLevel" class="block text-sm font-medium text-gray-700">Seriousness Level</label>
              <input
                id="seriousnessLevel"
                type="range"
                v-model="form.seriousnessLevel"
                min="1"
                max="3"
                class="mt-1 w-full"
              />
            </div>

            <PrimaryButton type="submit">Generate</PrimaryButton>
          </form>
          
          </div>
          <div v-if="alternativesREF.value && alternativesREF.length > 0" class="mt-4">
            <h3>Choose your Chirp:</h3>

            <div v-for="(alternative, index) in alternativesREF" :key="index" class="mb-4">
              <label :for="'alternative_' + index" class="block text-sm font-medium text-gray-700">
                Alternative {{ index + 1 }}
              </label>
              <textarea
                :id="'alternative_' + index"
                v-model="alternativesREF[index]"
                readonly
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
              >{{ alternative }}</textarea>
              <button
                @click.prevent="selectAlternative(alternative)"
                class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-md"
              >
                Select this Chirp
              </button>
            </div>

              <form @submit.prevent="handleGeneratedSubmit">
                <textarea
                  v-model="form.message"
                  class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                  placeholder="Your final Chirp message here..."
                ></textarea>
                <InputError :message="form.errors.message" class="mt-2" />
                <PrimaryButton class="mt-4">Chirp this Generated</PrimaryButton>
              </form>
            </div>


          </div>


            </form>
            <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
                <Chirp
                    v-for="chirp in chirps"
                    :key="chirp.id"
                    :chirp="chirp"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>