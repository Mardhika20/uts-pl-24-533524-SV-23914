
<template>
  <div class="auth-layout">
    <aside class="auth-visual">
      <img
        class="auth-background"
        src="/Foto%20soto%20ayam.svg"
        alt="Soto Ayam RecipeBox"
      />

      <div class="auth-overlay"></div>

      <div class="auth-brand">
        <img src="/recipebox-logo.svg" alt="Logo RecipeBox" />
        <span>RecipeBox</span>
      </div>

      <div class="auth-story">
        <p class="auth-eyebrow">RESEP PILIHAN, RASA RUMAHAN</p>
        <h2>Setiap masakan punya cerita.</h2>
        <p class="auth-description">
          Temukan inspirasi baru, simpan resep kesukaan,
          dan hadirkan kehangatan di meja makan.
        </p>
        <p class="auth-recipe">— &nbsp; Soto Ayam Kuning · 45 menit · 4 porsi</p>
      </div>
    </aside>

    <section class="auth-content">
      <RouterView />
    </section>
  </div>
</template>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 47.2% 52.8%;
  background: #f8f7f3;
}

.auth-visual {
  position: relative;
  min-height: 100vh;
  overflow: hidden;
  color: white;
}

.auth-background,
.auth-overlay {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
}

.auth-background {
  object-fit: cover;
}

.auth-overlay {
  background: linear-gradient(
    180deg,
    rgba(13, 35, 23, 0.4) 0%,
    rgba(13, 35, 23, 0.12) 40%,
    rgba(13, 35, 23, 0.9) 100%
  );
}

.auth-brand {
  position: absolute;
  top: 56px;
  left: 56px;
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 1;
  font-family: 'Lora', serif;
  font-size: 26px;
  font-weight: 600;
}

.auth-brand img {
  width: 36px;
  height: 36px;
  background: white;
  border-radius: 11px;
  padding: 5px;
}

.auth-story {
  position: absolute;
  left: 56px;
  right: 56px;
  bottom: 56px;
  z-index: 1;
}

.auth-eyebrow {
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 24px;
}

.auth-story h2 {
  font-family: 'Lora', serif;
  font-size: clamp(34px, 3.3vw, 48px);
  font-weight: 400;
  line-height: 1.15;
  margin: 0 0 24px;
}

.auth-description {
  font-size: 14px;
  line-height: 1.9;
  max-width: 520px;
  margin-bottom: 24px;
}

.auth-recipe {
  font-size: 12px;
  opacity: 0.85;
}

.auth-content {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px;
}

@media (max-width: 800px) {
  .auth-layout {
    grid-template-columns: 1fr;
  }

  .auth-visual {
    display: none;
  }

  .auth-content {
    min-height: 100vh;
    padding: 32px 20px;
  }
}
</style>
