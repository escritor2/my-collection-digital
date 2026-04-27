<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<h1 align="center">📚 Meu Acervo Digital</h1>

<p align="center">
  <strong>Plataforma moderna e inteligente para organizar leituras e integrar IA na sua estante virtual.</strong>
</p>

<p align="center">
  <a href="#-sobre-a-reestruturação">Sobre</a> •
  <a href="#-organização-backend-e-frontend">Arquitetura Front/Back</a> •
  <a href="#-análise-de-frameworks">Frameworks</a> •
  <a href="#-apis-para-enriquecimento">APIs Úteis</a> •
  <a href="#-integração-com-groq-ia">Integração com Groq</a>
</p>

---

## 📖 Sobre a Reestruturação

O **Meu Acervo Digital** nasceu como um monólito utilizando Laravel e Inertia.js. Com o crescimento da aplicação e o desejo de incorporar recursos avançados de Inteligência Artificial e enriquecimento de dados, estamos migrando para uma **arquitetura desacoplada**. Isso nos permite ter equipes trabalhando independentemente, escalar melhor cada serviço e utilizar as ferramentas mais adequadas para o Front-end, Back-end e IA.

---

## 🏗 Organização Backend e Frontend

Para garantir escalabilidade e manutenção facilitada, o projeto será dividido em duas frentes distintas:

1. **Repositório Backend (`/backend`)**
   - Fornecerá exclusivamente uma **API RESTful** ou **GraphQL**.
   - Gerenciamento de banco de dados, autenticação (Sanctum/JWT), jobs assíncronos e comunicação com serviços externos (Groq, Google Books).
   - Focado em performance e segurança.

2. **Repositório Frontend (`/frontend`)**
   - Uma aplicação **SPA** (Single Page Application) ou **SSR** (Server-Side Rendering).
   - Consumirá as APIs do backend para renderizar a interface de usuário de forma rica e responsiva.
   - Implementação de designs modernos, Micro-interações, Glassmorphism e suporte a PWA.

---

## 🛠 Análise de Frameworks

Ao separar as camadas, abrimos um leque de tecnologias. Abaixo está a análise das melhores opções para o novo formato:

### Frontend (Interface e UX)
* **Migração de Vue (Inertia) para Nuxt 3 (Vite) - [Altamente Recomendado]**:
  Como o projeto já utiliza Vue 3, a migração para **Nuxt 3** (que é impulsionado nativamente pelo **Vite**) é o caminho mais natural e vantajoso. 
  * **Vantagens da Migração:**
    - **SSR e SEO:** Diferente do Inertia (que atua majoritariamente no lado do cliente), o Nuxt oferece Server-Side Rendering (SSR). Isso permite que as páginas públicas da plataforma (como catálogo global de livros ou perfis de usuários) sejam lidas por motores de busca, atraindo tráfego orgânico.
    - **Developer Experience (DX):** O Nuxt traz auto-imports. Esqueça ter que importar componentes, `ref` ou `computed` manualmente em todo arquivo. O código ficará muito mais limpo.
    - **Roteamento Independente:** O frontend ganha independência. As rotas deixam de ser controladas pelo Laravel (`routes/web.php`) e passam a seguir a estrutura da pasta `pages/` do Nuxt.
    - **Performance do Vite:** Build e Hot Module Replacement (HMR) ultrarrápidos graças ao motor do Vite por baixo dos panos.
  * **Esforço de Transição:** Cerca de 80% do código dos componentes visuais `.vue` atuais pode ser reaproveitado diretamente. O principal esforço será remover os helpers do Inertia (`useForm`, `<Link>`) e substituí-los pelas ferramentas nativas do Nuxt (`useFetch`, `<NuxtLink>`), consumindo a nova API RESTful do backend.
* **Next.js (React)**: Líder de mercado, mas exigiria reescrever 100% das *views* que hoje estão em Vue. Descartado caso o objetivo seja produtividade e reaproveitamento de código.
* **Vite + Vue 3 (SPA Puro)**: Uma opção se não houver necessidade **nenhuma** de SEO (ex: se o sistema for totalmente privado). Porém, perde-se a estrutura robusta de roteamento, middlewares e renderização híbrida que o Nuxt já entrega pronto.

### Backend (API e Lógica de Negócio)
* **Laravel 12 (API Mode) - [Recomendado]**: Manter o Laravel apenas como API. É a transição mais suave, aproveitando os Models, Repositories e Migrations já existentes. O Laravel Octane pode ser adicionado para altíssima performance.
* **NestJS (Node.js)**: Uma excelente alternativa baseada em TypeScript com arquitetura muito semelhante ao Angular/Spring. Ideal se a equipe quiser unificar a linguagem (TypeScript) no Front e no Back.
* **FastAPI (Python)**: Como a IA será o coração de algumas novas features, um backend em Python com FastAPI lidaria de forma nativa e extremamente veloz com bibliotecas de Machine Learning, além da fácil integração com a API da Groq.

---

## 🌐 APIs para Enriquecimento de Dados

Para não exigir que o usuário digite título, autor, páginas e faça upload de capa manualmente, a plataforma consumirá bases de dados públicas de livros:

1. **Google Books API**
   - **Uso:** Busca principal. Ao inserir um ISBN ou título, retorna sinopse, número de páginas, capa em alta resolução e autores.
   - **Vantagens:** Gratuita e com a maior base de dados disponível.
2. **Open Library API**
   - **Uso:** Complemento ao Google Books. Excelente base de dados aberta, útil para livros antigos ou fora de circulação.
3. **The New York Times Books API**
   - **Uso:** Obter a lista atualizada dos "Mais Vendidos" (Bestsellers) para exibir na Home do aplicativo, servindo como recomendações padrão.
4. **Mercado Pago / Stripe**
   - **Uso:** Caso a plataforma adote planos Premium para funcionalidades extras de IA, limite de livros, ou doações para o projeto.

---

## 🤖 Integração com Groq (Inteligência Artificial)

A integração com o **Groq** transformará a plataforma de um simples gerenciador de estante para um "Assistente Literário Pessoal". O Groq utiliza LPUs (Language Processing Units) que entregam inferência para LLMs (como Llama 3) em velocidades absurdas (centenas de tokens por segundo).

### Casos de Uso na Plataforma:
1. **Recomendações Literárias Pessoais (Real-time):**
   - O usuário seleciona "Me recomende algo baseado nos livros da minha estante marcados com 5 estrelas".
   - O Backend gera um *prompt* combinando o gosto do usuário e envia ao Groq.
   - Em milissegundos, o Groq responde com uma curadoria altamente personalizada.
2. **Chatbot "Discuta o Livro":**
   - O usuário acabou de ler "1984" e quer discutir teorias.
   - Uma interface de chat (Frontend) se comunica via API (Backend) com o Groq, cujo contexto sistêmico é instruído a agir como um expert na obra analisada, promovendo debates literários sem dar spoilers indesejados.
3. **Geração Automática de Tags e Emoções:**
   - Ao adicionar a sinopse de um livro, o sistema roda um prompt no Groq para extrair: Sentimento principal (Sombrio, Alegre), Época, e Tags dinâmicas (Cyberpunk, Romance de Época).

### Arquitetura do Fluxo de IA (Backend):
```php
// Exemplo conceitual no Backend Laravel acessando o Groq
use Illuminate\Support\Facades\Http;

class GroqService {
    public function obterRecomendacao(array $livrosFavoritos) {
        $prompt = "Atue como um especialista literário. O usuário ama: " . implode(', ', $livrosFavoritos) . ". Recomende 3 livros novos em formato JSON.";
        
        $response = Http::withToken(env('GROQ_API_KEY'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama3-70b-8192', // Modelo rápido e inteligente
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'response_format' => ['type' => 'json_object']
            ]);

        return $response->json();
    }
}
```

---

## ✨ Funcionalidades Planejadas

- 🔐 **Autenticação Avançada**: JWT e Social Login (Google/Github).
- 📚 **Catálogo Inteligente**: Autocomplete de livros utilizando Google Books API.
- 🗂 **Estante Pessoal**: Organização em categorias e status.
- ⏱ **Tracker de Leitura**: Cronômetro de sessões e estatísticas (Páginas/hora).
- 🧠 **Smart Insights (Groq IA)**: Análise do comportamento de leitura e chat literário.
- 📊 **Dashboard Analytics**: Gráficos complexos e envolventes usando bibliotecas modernas.

---

## ⚙️ Como Executar (Visão do Novo Ambiente)

*Nota: O projeto está em fase de transição para a nova arquitetura.*

### Backend (API Laravel)
1. Acesse o diretório `/backend` (ou crie a estrutura).
2. Instale dependências: `composer install`.
3. Configure o `.env` (adicione `GROQ_API_KEY` e dados de banco).
4. Rode as migrations: `php artisan migrate`.
5. Inicie a API: `php artisan serve` (Rodando na porta 8000).

### Frontend (SPA/Nuxt/Vue)
1. Acesse o diretório `/frontend`.
2. Instale dependências: `npm install`.
3. Configure o `.env` para apontar `VITE_API_URL` para `http://localhost:8000/api`.
4. Inicie o modo desenvolvimento: `npm run dev`.

