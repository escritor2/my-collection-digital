# 📚 Acervo Digital - Sua Biblioteca Inteligente

<p align="center">
  <img src="./assets/banner.png" width="100%" alt="Acervo Digital Banner">
</p>

<p align="center">
  <b>A plataforma definitiva para organizar, ler e estudar seus livros com o poder da IA.</b>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Nuxt-00DC82?style=for-the-badge&logo=nuxt.js&logoColor=white" />
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Groq_AI-f55036?style=for-the-badge" />
  <img src="https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white" />
</p>

---

## ✨ Funcionalidades Principais

- 🔎 **Catálogo Inteligente**: Busque livros via Google Books, Open Library e iTunes com importação em um clique.
- 📖 **E-Reader Premium**: Leitor nativo para arquivos PDF e EPUB com salvamento automático de progresso.
- 🤖 **IA Anti-Spoiler (Groq)**: Converse com o livro! Tire dúvidas, peça resumos ou crie quizzes baseados apenas no que você já leu.
- 🏆 **Gamificação**: Ganhe XP e suba de nível enquanto lê e estuda.
- 🌐 **Social**: Compartilhe reviews, crie clubes de leitura e veja o que seus amigos estão lendo.
- 🌓 **Interface Moderna**: Design inspirado em glassmorphism com suporte total a modo escuro.

## 🛠️ Tecnologias Utilizadas

### Frontend
- **Framework**: [Nuxt 3](https://nuxt.com/)
- **UI Components**: [Shadcn Vue](https://www.shadcn-vue.com/)
- **Styling**: TailwindCSS & Vanilla CSS
- **Icons**: Lucide Vue Next

### Backend
- **Framework**: [Laravel 11](https://laravel.com/)
- **Auth**: Laravel Sanctum (Stateful)
- **Database**: SQLite (Desenvolvimento)
- **AI Engine**: Groq API (Llama 3.1)

## 🚀 Como Executar o Projeto

### Pré-requisitos
- PHP 8.2+
- Node.js 18+
- Composer
- Chave de API do [Groq](https://console.groq.com/) (Opcional, para o Chat)

### 1. Configuração do Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

**Nota**: No seu `.env`, configure a chave da IA:
`GROQ_API_KEY=sua_chave_aqui`

### 2. Configuração do Frontend
```bash
cd frontend
npm install
npm run dev
```
O frontend estará disponível em `http://localhost:3000`.

## 📖 Como usar o Chat com IA
1. Importe um livro no catálogo.
2. Na sua estante, clique em **Ler**.
3. Faça o upload do arquivo PDF ou EPUB.
4. Abra o painel lateral da IA e comece a estudar!

## 📄 Licença
Distribuído sob a licença MIT. Veja `LICENSE` para mais informações.

---

## 👥 Equipe

<p align="center">
  <img src="./assets/team_banner_v2.png" width="100%" alt="Acervo Digital Team">
</p>

<p align="center">
  Desenvolvido com ❤️ pela equipe do <b>Acervo Digital</b>.
</p>


