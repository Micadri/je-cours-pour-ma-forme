<?php
require __DIR__ . '/../config.php';
$user = requireAuth($pdo);

$data = json_decode(file_get_contents("php://input"), true);
$first_name = trim($data['first_name'] ?? '');
$audio_enabled = isset($data['audio_enabled']) ? (int)$data['audio_enabled'] : 1;
$theme = $data['theme'] ?? 'light';
$avatar = $data['avatar'] ?? null;

try {
    if ($avatar) {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, audio_enabled = ?, theme = ?, avatar = ? WHERE id = ?");
        $stmt->execute([$first_name, $audio_enabled, $theme, $avatar, $user['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, audio_enabled = ?, theme = ? WHERE id = ?");
        $stmt->execute([$first_name, $audio_enabled, $theme, $user['id']]);
    }
    
    echo json_encode(["status" => "success", "message" => "Profil mis à jour"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
```*(Pense aussi à ajouter `avatar` dans ton `SELECT` du fichier `progress.php` pour qu'il soit renvoyé au front-end !)*

**5. L'interface Profil (`frontend/src/views/ProfileView.vue`)**
On ajoute la prévisualisation, l'upload en Base64 et on met à jour la sauvegarde.
```vue
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramStore } from '../stores/program'

const router = useRouter()
const store = useProgramStore()

const firstName = ref(store.userProfile?.first_name || '')
const audioEnabled = ref(store.userProfile?.audio_enabled == 1)
const theme = ref(store.userProfile?.theme || 'light')
const avatarBase64 = ref(store.userProfile?.avatar || '')
const saveMessage = ref('')

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarBase64.value = e.target.result // Convertit l'image en texte Base64
    }
    reader.readAsDataURL(file)
  }
}

const saveProfile = async () => {
  await store.updateProfile({
    first_name: firstName.value,
    audio_enabled: audioEnabled.value ? 1 : 0,
    theme: theme.value,
    avatar: avatarBase64.value
  })
  saveMessage.value = 'Profil mis à jour !'
  setTimeout(() => saveMessage.value = '', 3000)
}
</script>

<template>
  <main style="padding: 20px; font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <button @click="router.push('/')" style="background: none; border: none; color: #4CAF50; font-size: 16px; font-weight: bold; cursor: pointer; margin-bottom: 20px;">
      ← Retour
    </button>
    
    <h1 style="margin-top: 0;">Mon Profil</h1>

    <div style="text-align: center; margin-bottom: 25px;">
      <div style="width: 100px; height: 100px; border-radius: 50%; background: #ccc; margin: 0 auto 10px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
        <img v-if="avatarBase64" :src="avatarBase64" style="width: 100%; height: 100%; object-fit: cover;" />
        <span v-else style="color: white; font-size: 30px;">🏃</span>
      </div>
      <input type="file" @change="handleFileUpload" accept="image/*" style="font-size: 14px;" />
    </div>

    <!-- Reste de ton formulaire (Prénom, Audio, Thème) ... -->
    <div style="background: #f4f4f4; padding: 20px; border-radius: 8px;">
      <!-- ... -->
      <button @click="saveProfile" style="width: 100%; padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer;">
        Sauvegarder
      </button>
      <p v-if="saveMessage" style="color: #4CAF50; text-align: center; margin-top: 15px; font-weight: bold;">{{ saveMessage }}</p>
    </div>
  </main>
</template>