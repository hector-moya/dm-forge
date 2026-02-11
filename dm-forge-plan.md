# DM Forge — Full Architecture & Implementation Plan

> A TALL-stack web app for Dungeon Masters to plan campaigns and run sessions live, powered by Laravel AI SDK.

---

## 1. Product Spec

### MVP (Weeks 1–4) — "Magical at the Table"

The MVP focuses on three pillars: **build → run → recap**. If a DM can prep a session in 30 minutes, run it smoothly at the table, and get a story recap afterward, the product delivers.

| Feature | Priority | Scope |
|---------|----------|-------|
| **Campaign CRUD + Bible** | P0 | Create campaign with core fields (lore, factions, locations, NPCs, theme). Export as Markdown. |
| **Session Builder** | P0 | Create sessions with scene list, encounters (monsters w/ stats), branching options (A/B/C), loot, notes. |
| **Session Runner** | P0 | Initiative tracker, HP tracking, conditions, scene reveal, fast session log, "Major Decision" button. |
| **Alignment Compass** | P0 | Two-axis score per character. Manual adjust + AI suggestion on plain-language action input. DM override. |
| **End-of-Session Recap** | P0 | AI-generated story narrative, bullet recap, next-session hooks from session log. |
| **AI: Session Outline Generator** | P1 | Given campaign context + a premise, generate a full session outline with scenes, encounters, branches. |
| **AI: Consequence Generator** | P1 | Given a player decision, suggest immediate + delayed + meta consequences. |
| **Export** | P1 | Campaign Bible → Markdown/PDF. Session recap → Markdown. |

### v2+ (Deferred)

| Feature | Notes |
|---------|-------|
| Campaign Wizard (guided multi-step) | Replace free-form creation with a step-by-step wizard with AI co-pilot at each step. |
| NPC generator with personality/voice | AI tool: `create_npc` with backstory, motivation, voice notes. |
| Encounter balancer | AI evaluates party level vs monster CR and suggests adjustments. |
| World state timeline | Visual timeline of faction movements, consequence chains, world events. |
| Puzzle designer | AI-assisted puzzle creation with hint tiers. |
| Special mechanic tracker | Generic "tracker" system (battery ticks, lives, custom counters). |
| Multi-user / player view | Players see their character sheet + alignment compass (read-only). |
| JSON import/export | Full campaign portability. |
| Filament admin panels | Bulk management of monsters, loot tables, factions. |
| Audio narration (TTS) | Use Laravel AI SDK `Audio` to narrate recaps. |

---

## 2. Data Model

### Entity Relationship Diagram (Text)

```
User 1──M Campaign 1──M Session
                    1──M Character
                    1──M Faction
                    1──M Location
                    1──M Npc

Session 1──M Scene
        1──M Encounter 1──M EncounterMonster
        1──M SessionLog
        1──M BranchOption 1──M Consequence

Character 1──M AlignmentEvent

Campaign 1──M Tag (polymorphic taggable)
```

### Tables & Key Fields

```sql
-- campaigns
id, user_id, name, premise, lore, world_rules, theme_tone,
special_mechanics (json), status (draft/active/archived),
bible_cache (text, rendered markdown), timestamps

-- factions
id, campaign_id, name, description, alignment, goals, resources,
relationships (json), sort_order, timestamps

-- locations
id, campaign_id, name, description, region, tags (json),
parent_location_id (self-ref for sub-locations), timestamps

-- npcs
id, campaign_id, name, role, description, personality,
motivation, stats (json), faction_id (nullable),
location_id (nullable), is_alive (bool), timestamps

-- characters (PCs)
id, campaign_id, name, player_name, class, level,
hp_max, hp_current, armor_class, stats (json),
good_evil_score (int, default 0, range -10..+10),
law_chaos_score (int, default 0, range -10..+10),
alignment_label (varchar), notes, timestamps

-- sessions
id, campaign_id, title, session_number, type (one_shot/sequential),
status (draft/prepared/running/completed),
setup_text, recap_text, dm_notes,
generated_narrative (text), generated_bullets (text),
generated_hooks (text), generated_world_state (text),
started_at, ended_at, timestamps

-- scenes
id, session_id, title, description, sort_order,
is_revealed (bool, default false), notes, timestamps

-- encounters
id, session_id, scene_id (nullable), name, description,
environment, difficulty, sort_order, timestamps

-- encounter_monsters
id, encounter_id, name, hp_max, hp_current, armor_class,
initiative, stats (json), conditions (json),
notes, sort_order, timestamps

-- branch_options
id, session_id, scene_id (nullable), label (e.g. "Option A"),
description, sort_order, chosen (bool, default false), timestamps

-- consequences
id, branch_option_id, type (immediate/delayed/meta),
description, resolved (bool, default false),
resolved_at, timestamps

-- session_logs
id, session_id, entry (text), type (narrative/decision/combat/note),
tags (json), logged_at (datetime), timestamps

-- alignment_events
id, character_id, session_id, action_description,
tags (json — harm/mercy/order/rebellion etc.),
good_evil_delta (int), law_chaos_delta (int),
ai_suggested_ge (int), ai_suggested_lc (int),
dm_overridden (bool), timestamps

-- tags (polymorphic)
id, taggable_type, taggable_id, label, category
(tone/theme/faction/region), timestamps
```

### Key Indexes

```sql
-- Fast session queries
INDEX idx_sessions_campaign_status ON sessions(campaign_id, status);
INDEX idx_session_logs_session ON session_logs(session_id, logged_at);
INDEX idx_scenes_session_sort ON scenes(session_id, sort_order);
INDEX idx_alignment_events_char ON alignment_events(character_id, created_at);
```

---

## 3. Livewire Component Architecture

### Route Structure

```
/                           → Dashboard (campaign list)
/campaigns/create           → CampaignCreate
/campaigns/{id}             → CampaignShow (bible view)
/campaigns/{id}/edit        → CampaignEdit
/campaigns/{id}/sessions    → SessionIndex
/sessions/create?campaign=  → SessionBuilder
/sessions/{id}/edit         → SessionBuilder (edit mode)
/sessions/{id}/run          → SessionRunner ← THE KEY SCREEN
/sessions/{id}/recap        → SessionRecap (generate + view)
/campaigns/{id}/characters  → CharacterIndex
/characters/{id}/alignment  → AlignmentCompass
```

### Major Livewire Components

```
App\Livewire\
├── Dashboard                       # Campaign list, quick-start
├── Campaigns\
│   ├── CampaignCreate              # Form: name, premise, lore, theme, etc.
│   ├── CampaignShow                # Rendered bible, faction/NPC/location cards
│   ├── CampaignEdit                # Full edit form
│   └── CampaignExport              # PDF/Markdown export trigger
├── Sessions\
│   ├── SessionIndex                # Session list for a campaign
│   ├── SessionBuilder              # Full session editor
│   │   ├── SceneEditor (nested)    # Inline scene CRUD, drag-sort
│   │   ├── EncounterEditor         # Monster stat blocks, initiative
│   │   ├── BranchEditor            # Option A/B/C with consequences
│   │   └── AiOutlinePanel          # Sidebar: AI generates outline
│   ├── SessionRunner               # ★ LIVE PLAY SCREEN ★
│   │   ├── InitiativeTracker       # PC + NPC initiative, HP, conditions
│   │   ├── SceneRevealer           # One-click show prepared scenes
│   │   ├── QuickLog                # Fast text input → timestamped log
│   │   ├── DecisionRecorder        # "Major Moral Decision" modal
│   │   └── CombatPanel             # HP adjustment, condition toggle
│   └── SessionRecap                # AI-generated narrative view
├── Characters\
│   ├── CharacterIndex              # Character list for campaign
│   ├── CharacterForm               # Create/edit character
│   └── AlignmentCompass            # Two-axis visual + event history
│       ├── AlignmentInput          # Plain-language action → suggestion
│       └── AlignmentChart          # Alpine.js canvas/SVG compass
└── Shared\
    ├── AiStreamingPanel            # Reusable: shows streamed AI output
    ├── TagInput                    # Reusable tag selector
    ├── ConfirmModal                # Reusable confirmation dialog
    └── MarkdownRenderer            # Renders markdown to HTML safely
```

### Key UX Patterns

- **SessionRunner** uses `wire:poll` sparingly; most updates are user-driven clicks.
- **InitiativeTracker** uses Alpine.js for drag-sort and instant HP math (no server round-trip for +/- HP).
- **QuickLog** uses `wire:submit` with auto-timestamp and auto-scroll.
- **AiStreamingPanel** uses Laravel AI SDK streaming → SSE endpoint → Alpine `EventSource` listener.
- **DecisionRecorder** is a modal with: action text, character select, optional tags, then fires alignment event creation + AI suggestion.

---

## 4. AI Architecture (Laravel AI SDK)

### Provider Strategy & Cost Controls

| Task | Model | Provider | Why |
|------|-------|----------|-----|
| Classification/tagging (action → alignment tags) | `claude-haiku-4-5` or `gpt-4o-mini` | Anthropic/OpenAI | Fast, cheap, structured output |
| Alignment score suggestion | `claude-haiku-4-5` or `gpt-4o-mini` | Anthropic/OpenAI | Simple numeric reasoning |
| Session outline generation | `claude-sonnet-4-5` or `gpt-4o` | Anthropic/OpenAI | Needs creativity + structure |
| Consequence/branch generation | `claude-sonnet-4-5` or `gpt-4o` | Anthropic/OpenAI | Nuanced narrative reasoning |
| End-of-session narrative | `claude-sonnet-4-5` or `gpt-4o` | Anthropic/OpenAI | Best prose quality, longest output |
| Campaign wizard co-pilot (v2) | `claude-sonnet-4-5` | Anthropic | Conversational, creative |

### Agents

```
App\Ai\Agents\
├── AlignmentAdvisor          # HasStructuredOutput — suggests score deltas
├── SessionOutliner           # HasTools — generates session structure
├── ConsequenceGenerator      # HasStructuredOutput — immediate/delayed/meta
├── NarrativeWriter           # Streaming — end-of-session story
├── CampaignWizardHelper      # Conversational, HasTools — v2 wizard co-pilot
└── TagClassifier             # HasStructuredOutput — classifies action into tags
```

### Agent: AlignmentAdvisor

```php
class AlignmentAdvisor implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        public Character $character,
        public string $actionDescription,
        public array $tags = [],
    ) {}

    public function instructions(): string
    {
        return <<<PROMPT
        You are an impartial D&D alignment analyst. Given a character's action,
        suggest adjustments to their Good↔Evil (-10 to +10) and Law↔Chaos
        (-10 to +10) scores.

        Rules:
        - Small mundane actions: ±0 or ±1
        - Significant moral choices: ±2 to ±3
        - Extreme acts (murder of innocents, self-sacrifice): ±4 to ±5
        - NEVER jump more than ±5 in one event
        - Consider context: a Lawful Good paladin breaking the law to save
          a child is complex — reflect that nuance
        - Provide a brief reasoning (1-2 sentences)

        Current scores: Good/Evil = {$this->character->good_evil_score},
        Law/Chaos = {$this->character->law_chaos_score}
        Character: {$this->character->name} ({$this->character->class}, Level {$this->character->level})
        PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'good_evil_delta' => $schema->integer()->min(-5)->max(5)->required(),
            'law_chaos_delta' => $schema->integer()->min(-5)->max(5)->required(),
            'reasoning' => $schema->string()->required(),
        ];
    }
}
```

**Usage:**

```php
$response = (new AlignmentAdvisor($character, $action, $tags))
    ->prompt(
        "Action: {$action}\nTags: " . implode(', ', $tags),
        model: 'claude-haiku-4-5-20251001',
    );

// $response['good_evil_delta'], $response['law_chaos_delta'], $response['reasoning']
```

### Agent: NarrativeWriter (Streaming)

```php
class NarrativeWriter implements Agent
{
    use Promptable;

    public function __construct(
        public Session $session,
    ) {}

    public function instructions(): string
    {
        $campaign = $this->session->campaign;
        $logs = $this->session->logs()->orderBy('logged_at')->get()
            ->map(fn ($l) => "[{$l->logged_at->format('H:i')}] ({$l->type}) {$l->entry}")
            ->implode("\n");
        $decisions = $this->session->alignmentEvents()
            ->with('character')->get()
            ->map(fn ($e) => "{$e->character->name}: {$e->action_description}")
            ->implode("\n");

        return <<<PROMPT
        You are a mythic storyteller chronicling a D&D campaign.
        Write in a style that is: {$campaign->theme_tone}.

        CAMPAIGN: {$campaign->name}
        PREMISE: {$campaign->premise}

        SESSION #{$this->session->session_number}: {$this->session->title}
        SETUP: {$this->session->setup_text}

        SESSION LOG:
        {$logs}

        KEY DECISIONS:
        {$decisions}

        RULES:
        - Write a 1-3 page narrative recap of what happened this session
        - Consequences should feel fair, earned, and morally sharp — never "DM gotcha"
        - Do NOT invent events that aren't in the log
        - End with a hook that makes players excited for next session
        - After the narrative, provide:
          ## Bullet Recap (5-10 bullets)
          ## Next Session Hooks (2-3 hooks)
          ## World State Changes (NPCs changed, factions moved, consequences pending)
        PROMPT;
    }
}
```

**Streamed to the UI:**

```php
// In a route or controller
return (new NarrativeWriter($session))
    ->stream("Generate the session recap.")
    ->then(function (StreamedAgentResponse $response) use ($session) {
        $session->update([
            'generated_narrative' => $response->text,
            'status' => 'completed',
        ]);
    });
```

### Tools

```
App\Ai\Tools\
├── LookupNpc          # Searches NPCs by name/faction for context injection
├── LookupLocation     # Searches locations for scene context
├── GetSessionLogs     # Retrieves session log entries
├── GetCharacterSheet  # Returns character stats + alignment history
├── CreateNpc          # (v2) Creates NPC in database from AI suggestion
└── RollDice           # Generates dice roll results (fun but useful)
```

**Example Tool: LookupNpc**

```php
class LookupNpc implements Tool
{
    public function __construct(public Campaign $campaign) {}

    public function description(): string
    {
        return 'Search for NPCs in the current campaign by name or faction.';
    }

    public function handle(Request $request): string
    {
        $npcs = Npc::where('campaign_id', $this->campaign->id)
            ->when($request['name'] ?? null, fn ($q, $name) =>
                $q->where('name', 'like', "%{$name}%"))
            ->when($request['faction_id'] ?? null, fn ($q, $fid) =>
                $q->where('faction_id', $fid))
            ->limit(5)->get();

        return $npcs->map(fn ($n) =>
            "{$n->name} — {$n->role}. {$n->description}"
        )->implode("\n\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string(),
            'faction_id' => $schema->integer(),
        ];
    }
}
```

### Memory Strategy

| Pattern | Implementation |
|---------|---------------|
| **Campaign context** | Injected into system prompt from Eloquent models (not conversation memory). Rebuilt per request. |
| **Session log as context** | Session logs are loaded and injected as structured text in the system prompt. |
| **Wizard conversation (v2)** | Uses `RemembersConversations` trait. `forUser($user)` / `continue($conversationId)`. |
| **Caching** | Cache rendered campaign bible (invalidate on edit). Cache alignment suggestion for same action hash (5 min TTL). |

### Safety & Guardrails

All system prompts include:

```
GUARDRAILS:
- You are a creative assistant for a Dungeon Master. You serve the DM's vision.
- NEVER railroad: do not suggest that any choice is "correct" or "wrong."
- NEVER punish players for making unexpected choices. Consequences should be
  logical, fair, and narratively interesting.
- Present all options as equally viable with different trade-offs.
- If generating content that could be sensitive (torture, betrayal, death),
  frame it with narrative weight and gravity, never flippancy.
- Output structured JSON when asked for data. Output prose when asked for narrative.
- Do not break character or reference being an AI. Write as the campaign's narrator.
- Respect the DM's world rules and special mechanics exactly as described.
```

### Cost Controls

```php
// config/ai.php — define model aliases
'models' => [
    'fast' => 'claude-haiku-4-5-20251001',   // ~$0.25/M input
    'smart' => 'claude-sonnet-4-5-20250929',  // ~$3/M input
],

// In agents, use model parameter
->prompt($text, model: config('ai.models.fast'))  // tagging, alignment
->prompt($text, model: config('ai.models.smart')) // narrative, outlines

// Rate limiting in middleware
RateLimiter::for('ai', function (Request $request) {
    return Limit::perMinute(20)->by($request->user()->id);
});
```

**Token budget per feature:**

| Feature | Max tokens | Model tier |
|---------|-----------|------------|
| Alignment suggestion | ~200 | fast |
| Tag classification | ~100 | fast |
| Session outline | ~2000 | smart |
| Consequence generation | ~500 | smart |
| Narrative recap | ~4000 | smart |

---

## 5. Step-by-Step Implementation Plan

### Week 1: Foundation + Campaign + Characters

```
Day 1-2: Project Setup
  □ laravel new dm-forge (Livewire starter kit)
  □ Configure SQLite, Tailwind v4, Alpine
  □ composer require laravel/ai
  □ php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
  □ php artisan migrate
  □ Set up .env with ANTHROPIC_API_KEY (or OPENAI_API_KEY)
  □ Create base layout with dark-mode-friendly UI

Day 3-4: Campaign CRUD
  □ Create migrations: campaigns, factions, locations, npcs
  □ Create Eloquent models with relationships
  □ Build Livewire CampaignCreate, CampaignEdit, CampaignShow
  □ Build CampaignShow as "Campaign Bible" — rendered markdown view
  □ Add faction/location/NPC inline CRUD on campaign edit page

Day 5: Characters + Alignment Model
  □ Create migrations: characters, alignment_events
  □ Build CharacterIndex, CharacterForm Livewire components
  □ Build AlignmentCompass page: two-axis chart (Alpine.js SVG)
  □ Manual alignment adjustment (slider or direct input)
```

### Week 2: Session Builder + AI Agents

```
Day 6-7: Session Builder
  □ Create migrations: sessions, scenes, encounters, encounter_monsters,
    branch_options, consequences, session_logs
  □ Build SessionBuilder Livewire page
  □ Implement SceneEditor (nested, sortable)
  □ Implement EncounterEditor with monster stat block fields
  □ Implement BranchEditor (Option A/B/C + consequence sub-forms)

Day 8-9: AI Agents — Foundation
  □ php artisan make:agent AlignmentAdvisor --structured
  □ php artisan make:agent SessionOutliner
  □ php artisan make:agent ConsequenceGenerator --structured
  □ php artisan make:agent NarrativeWriter
  □ php artisan make:agent TagClassifier --structured
  □ Implement prompt templates (see Section 6)
  □ Build shared AiStreamingPanel Livewire component

Day 10: AI Integration — Alignment
  □ Wire AlignmentAdvisor to AlignmentCompass page
  □ "Describe action" → AI suggests → DM approves/overrides → save
  □ Test with sample actions, verify structured output
```

### Week 3: Session Runner + Narrative

```
Day 11-13: Session Runner (THE BIG BUILD)
  □ Build SessionRunner full-page Livewire component
  □ InitiativeTracker: add PCs + monsters, roll/set initiative,
    sort order, current-turn highlight
  □ HP tracking: click +/- buttons, Alpine instant math
  □ Conditions: dropdown toggle (poisoned, stunned, etc.)
  □ SceneRevealer: clickable scene cards, mark as revealed
  □ QuickLog: text input + type dropdown → timestamped entry
  □ DecisionRecorder modal:
    - Select character, describe action, pick tags
    - Fire AlignmentAdvisor, show suggestion
    - DM approves or overrides, save alignment_event
    - Record in session_log with type=decision

Day 14-15: End-of-Session Recap
  □ Build SessionRecap page
  □ "Generate Recap" button → streams NarrativeWriter
  □ Display streamed output in real-time (SSE → Alpine)
  □ Parse sections (narrative, bullets, hooks, world state)
  □ Save to session fields
  □ Add "Regenerate" option
```

### Week 4: Polish + AI Features + Export

```
Day 16-17: AI Session Outliner + Consequences
  □ Add AiOutlinePanel to SessionBuilder sidebar
  □ "Generate outline" → SessionOutliner produces scenes/encounters
  □ DM can accept/reject/edit individual items
  □ ConsequenceGenerator: select a branch option → AI suggests consequences
  □ Wire into BranchEditor

Day 18-19: Export + Campaign Bible
  □ Campaign Bible → Markdown export (collect all campaign data, render)
  □ Session recap → Markdown export
  □ Optional: PDF export via headless Chrome or wkhtmltopdf

Day 20: Testing + Polish
  □ Write feature tests for Session Runner flows
  □ Fake AI responses in tests (Laravel AI SDK testing utilities)
  □ UI polish: loading states, error handling, responsive layout
  □ Performance: eager loading, query optimization
  □ README + deployment notes
```

---

## 6. Example AI Prompt Templates

### Prompt 1: Campaign Wizard Step Helper (v2, shown for completeness)

**System:**
```
You are a D&D campaign design assistant helping a Dungeon Master build their
campaign world. You are currently helping with the "{step_name}" step.

The DM has established the following so far:
{existing_campaign_context}

GUIDELINES:
- Ask clarifying questions if the DM's input is vague
- Suggest 2-3 options when the DM seems stuck, but never force a direction
- Respect the DM's creative authority absolutely
- Keep suggestions tonally consistent with: {theme_tone}
- If the DM has defined special mechanics, incorporate them naturally
- Output valid JSON when creating structured data, prose when brainstorming

GUARDRAILS:
- Never suggest content that contradicts the DM's established lore
- Never railroad: all options are equally valid
- Never reference being an AI
```

**User:**
```
I'm working on factions for my campaign. The premise is: "{premise}"

I have these factions so far: {existing_factions_json}

Help me flesh out the relationships between these factions, and suggest
if any faction archetype is missing that would create interesting tension.
```

### Prompt 2: Session Outline Generator

**System:**
```
You are a D&D session planner. Given a campaign context and a session premise,
generate a complete session outline.

CAMPAIGN: {campaign_name}
PREMISE: {campaign_premise}
THEME/TONE: {theme_tone}
WORLD RULES: {world_rules}
SPECIAL MECHANICS: {special_mechanics}

ACTIVE FACTIONS: {faction_summaries}
KEY NPCS: {npc_summaries}
PREVIOUS SESSION SUMMARY: {last_session_recap}
PENDING CONSEQUENCES: {unresolved_consequences}

CHARACTERS IN PARTY:
{character_summaries_with_alignment}

OUTPUT FORMAT (JSON):
{
  "title": "session title",
  "setup_text": "what the DM reads to set the scene",
  "scenes": [
    {
      "title": "scene name",
      "description": "what happens",
      "sort_order": 1,
      "notes": "DM tips"
    }
  ],
  "encounters": [
    {
      "name": "encounter name",
      "scene_index": 0,
      "description": "setup",
      "difficulty": "medium",
      "monsters": [
        { "name": "Goblin", "hp_max": 7, "armor_class": 15, "count": 4 }
      ]
    }
  ],
  "branch_options": [
    {
      "scene_index": 1,
      "label": "Option A: Negotiate",
      "description": "The party attempts diplomacy",
      "consequences": [
        { "type": "immediate", "description": "The chief agrees to parley" },
        { "type": "delayed", "description": "The tribe remembers this mercy" }
      ]
    }
  ],
  "estimated_duration_minutes": 180
}

RULES:
- Include 3-5 scenes with natural flow
- Include at least one encounter and one meaningful choice
- Branch options should feel equally viable with different trade-offs
- Reference pending consequences from previous sessions
- Incorporate the party's alignment tendencies into NPC reactions
- NEVER make one option "correct" — each path leads to interesting play
```

**User:**
```
Plan a session where: "{session_premise}"
```

### Prompt 3: Consequence/Branch Generator

**System:**
```
You are a D&D consequence analyst. Given a player decision and campaign context,
generate layered consequences.

CAMPAIGN CONTEXT:
- Theme/tone: {theme_tone}
- Active factions: {faction_names}
- World rules: {world_rules}

RULES:
- Every decision has ripples. Think in three layers:
  IMMEDIATE: What happens in the next 5 minutes of game time
  DELAYED: What happens 1-3 sessions later (faction reactions, NPC changes, reputation)
  META: How this shifts the campaign's trajectory or theme
- Consequences must be LOGICAL, not punitive
- Even "good" choices have costs; even "bad" choices have silver linings
- Reference specific factions and NPCs by name when relevant
- Special mechanics affected: {special_mechanics}

OUTPUT (JSON):
{
  "consequences": [
    { "type": "immediate", "description": "..." },
    { "type": "immediate", "description": "..." },
    { "type": "delayed", "description": "..." },
    { "type": "delayed", "description": "..." },
    { "type": "meta", "description": "..." }
  ],
  "affected_npcs": ["npc_name"],
  "affected_factions": ["faction_name"],
  "tone_note": "brief note on how this lands emotionally"
}
```

**User:**
```
The party just made this choice: "{decision_description}"
Context: {scene_context}
Characters involved: {character_names}
Witnesses: {witness_npcs}
```

### Prompt 4: Alignment Adjustment Suggestion

**System:**
```
You are a D&D alignment analyst. Evaluate a character action and suggest score
adjustments on two axes:
- Good (+) ↔ Evil (-): scale -10 to +10
- Law (+) ↔ Chaos (-): scale -10 to +10

CHARACTER: {name} ({class}, Level {level})
CURRENT SCORES: Good/Evil = {ge_score}, Law/Chaos = {lc_score}
ALIGNMENT HISTORY (last 5 events):
{recent_alignment_events}

SCORING GUIDE:
- Trivial/ambiguous actions: ±0
- Minor but clear moral valence: ±1
- Significant moral choice: ±2 to ±3
- Extreme acts (murder innocents, heroic self-sacrifice): ±4 to ±5
- NEVER exceed ±5 in a single event
- Consider the character's established pattern — a first offense hits harder
  than repeated behavior
- "Lawful" means: keeps promises, respects authority, follows codes
- "Chaotic" means: values freedom, breaks rules for personal belief, improvises
- "Good" means: protects life, shows mercy, self-sacrifice
- "Evil" means: harms for gain, cruelty, disregard for life

OUTPUT (JSON):
{
  "good_evil_delta": <int -5..5>,
  "law_chaos_delta": <int -5..5>,
  "reasoning": "<1-2 sentences>"
}
```

**User:**
```
Action: "{action_description}"
Tags: {tags}
Scene context: {scene_context}
```

### Prompt 5: End-of-Session Narrative Generator

**System:**
```
You are the mythic narrator of a D&D campaign. Your prose style is:
{theme_tone} — think Ursula K. Le Guin meets Joe Abercrombie.
Consequences should feel earned, fair, and morally sharp.

CAMPAIGN: {campaign_name}
PREMISE: {campaign_premise}
WORLD RULES: {world_rules}

SESSION #{session_number}: {session_title}
DM'S SETUP: {setup_text}

CHARACTERS:
{character_details_with_alignment}

PREPARED SCENES:
{scene_list}

KEY DECISIONS MADE:
{alignment_events_with_reasoning}

FULL SESSION LOG (chronological):
{session_log_entries}

BRANCH OPTIONS CHOSEN:
{chosen_branches_with_consequences}

INSTRUCTIONS:
1. Write a narrative recap (800-2000 words) that transforms the session log
   into vivid prose. Stay faithful to what actually happened.
2. Give each PC at least one moment in the spotlight.
3. Consequences of decisions should feel FAIR — not "gotcha" moments.
4. End with a cliffhanger or hook that makes players eager for next session.
5. After the narrative, include these sections:

## Bullet Recap
- 5-10 key events as concise bullets

## Next Session Hooks
- 2-3 specific hooks the DM can use to open next session

## World State Changes
- NPCs: who changed allegiance, mood, status, or died
- Factions: who gained/lost power or shifted stance
- Consequences Pending: unresolved threads to track

DO NOT:
- Invent events not in the session log
- Make any player choice seem "wrong"
- Use modern slang or break the mythic tone
- Reference game mechanics (HP, AC, dice rolls) — translate to narrative
```

**User:**
```
Generate the session recap for Session #{session_number}.
```

---

## 7. Session Runner UX Flow

### Screen Layout (Desktop — Primary Use Case)

```
┌─────────────────────────────────────────────────────────────┐
│  ⚔ SESSION RUNNER — "The Crimson Vow" (Session #4)  [End]  │
├──────────────────────────────────┬──────────────────────────┤
│                                  │                          │
│  ┌──────────────────────────┐    │  SCENES                  │
│  │ INITIATIVE TRACKER       │    │  ┌────────────────────┐  │
│  │                          │    │  │ ◉ The Docks at Dawn │  │
│  │  ► 18  Kira (Rogue)     │    │  │   [Reveal] [Notes]  │  │
│  │    15  Bandit Captain ❤12│    │  ├────────────────────┤  │
│  │    14  Thorne (Paladin)  │    │  │ ○ The Warehouse     │  │
│  │    11  Bandit x3    ❤7ea│    │  │   [Reveal] [Notes]  │  │
│  │    8   Lyra (Wizard)     │    │  ├────────────────────┤  │
│  │                          │    │  │ ○ The Choice        │  │
│  │  [Next Turn] [Add NPC]  │    │  │   [Reveal] [Notes]  │  │
│  │  [Roll Initiative]       │    │  └────────────────────┘  │
│  └──────────────────────────┘    │                          │
│                                  │  QUICK ACTIONS            │
│  ┌──────────────────────────┐    │  ┌────────────────────┐  │
│  │ COMBAT PANEL             │    │  │ ★ Major Decision    │  │
│  │                          │    │  │ 📝 Quick Note       │  │
│  │  Bandit Captain: 12/24HP │    │  │ 🎲 Random Table     │  │
│  │  [-1] [-5] [-10] [Custom]│    │  └────────────────────┘  │
│  │  [+5] [Heal Full]        │    │                          │
│  │                          │    │  BRANCH OPTIONS           │
│  │  Conditions:             │    │  ┌────────────────────┐  │
│  │  [Poisoned] [Stunned]...│    │  │ A: Negotiate ☐     │  │
│  └──────────────────────────┘    │  │ B: Attack    ☐     │  │
│                                  │  │ C: Flee      ☐     │  │
│  ┌──────────────────────────┐    │  │ [Show Consequences] │  │
│  │ SESSION LOG              │    │  └────────────────────┘  │
│  │                          │    │                          │
│  │ 19:42 Players entered    │    │  DM NOTES                │
│  │       the docks...       │    │  ┌────────────────────┐  │
│  │ 19:55 Combat initiated   │    │  │ Remember: merchant  │  │
│  │       with bandits       │    │  │ has info about the  │  │
│  │ 20:10 Kira intimidated   │    │  │ cult. Don't reveal  │  │
│  │       the captain        │    │  │ until asked...      │  │
│  │                          │    │  └────────────────────┘  │
│  │ [+ Add Log Entry______]  │    │                          │
│  └──────────────────────────┘    │                          │
└──────────────────────────────────┴──────────────────────────┘
```

### Interaction Flow: Running a Session

```
1. DM opens Session Runner → Status changes to "running", started_at set

2. INITIATIVE PHASE
   - Click "Roll Initiative" → pre-populates PCs from characters table
   - Add encounter monsters from prepared encounters
   - Set/roll initiative values → auto-sort descending
   - Click "Next Turn" to advance highlight

3. SCENE FLOW
   - Click "Reveal" on a scene → scene card expands with description
   - Scene description visible only to DM (no player screen in MVP)
   - DM narrates from revealed content

4. COMBAT
   - Click any creature in initiative → Combat Panel shows their stats
   - HP buttons: [-1] [-5] [-10] [Custom] / [+5] [Heal Full]
   - HP updates instantly via Alpine (optimistic UI, wire:click syncs)
   - Toggle conditions from dropdown (visual badge appears on tracker)
   - When HP = 0: creature grayed out, "(down)" label

5. SESSION LOG (runs continuously)
   - Text input at bottom: DM types "Kira snuck behind the captain"
   - Press Enter → timestamped entry added, auto-scrolls
   - Type selector: narrative (default), decision, combat, note
   - Entries are simple, fast, no friction

6. MAJOR DECISION (the magic moment)
   - DM clicks "★ Major Decision" → modal opens
   - Fields:
     - Which character(s)? [multi-select from party]
     - What happened? [text area — "Kira spared the bandit captain"]
     - Tags: [harm] [mercy] [deception] [order] [rebellion] [sacrifice]
   - Click "Get AI Suggestion" →
     - AlignmentAdvisor returns: GE: +2, LC: -1
       "Showing mercy to a defeated enemy is a Good act (+2), but
        defying the party's agreed plan to kill him is mildly Chaotic (-1)."
   - DM can adjust sliders, then "Confirm"
   - Saves: alignment_event + session_log entry (type=decision)
   - Character's compass updates in real-time

7. BRANCH OPTIONS
   - When a scene has branches, they appear in right panel
   - DM checks which option the party chose
   - Consequences reveal below (if pre-written)
   - "Show Consequences" button if DM wants to see them mid-session

8. END SESSION
   - DM clicks "End Session" → confirms
   - Redirected to SessionRecap page
   - "Generate Recap" → NarrativeWriter streams the story
   - DM reviews, can regenerate or edit
   - Export to Markdown
```

### Key UX Principles for Table Play

| Principle | Implementation |
|-----------|----------------|
| **One-click everything** | No multi-step forms during combat. HP adjustment is a single click. |
| **No page reloads** | Livewire + Alpine keep everything on one screen. |
| **Big touch targets** | HP buttons are large. Initiative list items have generous padding. |
| **Keyboard shortcuts** | `Enter` for log, `N` for next turn, `M` for major decision (Alpine `@keydown`). |
| **Dark mode default** | Easier on eyes during long sessions. Reduce glare at dim tables. |
| **Minimal AI latency** | Alignment suggestion uses `fast` model (~1s). Narrative uses streaming. |
| **Offline-resilient** | All tracker state is in Livewire component state. Log entries queue locally if connection drops (Alpine). |

---

## 8. Sample Data Objects

### Campaign + Session + Characters — Example

```json
{
  "campaign": {
    "id": 1,
    "name": "The Sundered Covenant",
    "premise": "The gods are dying. Divine magic is fading. Three factions race to claim the last divine spark before the world falls into permanent twilight.",
    "lore": "A thousand years ago, the gods forged a covenant binding them to the mortal realm...",
    "theme_tone": "mythic, morally grey, cosmic horror undertones, hope is earned not given",
    "world_rules": "Divine magic requires proximity to a Covenant Shard. Clerics beyond 1 mile of a shard cast at disadvantage.",
    "special_mechanics": {
      "covenant_shards": "7 shards exist. Possessing one grants divine casting but slowly corrupts.",
      "twilight_clock": "Each session without a shard recovered, the twilight advances. Track on a d10: starts at 1, at 10 the sun dies."
    },
    "status": "active"
  },

  "factions": [
    {
      "id": 1,
      "name": "The Dawnkeepers",
      "description": "Paladins and clerics desperately gathering shards to restore the gods.",
      "alignment": "Lawful Good",
      "goals": "Collect all 7 shards, perform the Rite of Rekindling"
    },
    {
      "id": 2,
      "name": "The Ashen Parliament",
      "description": "Arcane scholars who believe divine magic is a cage and the gods' death is liberation.",
      "alignment": "Lawful Neutral",
      "goals": "Destroy the shards, usher in an age of pure arcane power"
    },
    {
      "id": 3,
      "name": "The Hollow Court",
      "description": "Undead nobility using the twilight to expand their domain.",
      "alignment": "Neutral Evil",
      "goals": "Eternal twilight serves them. Sabotage both sides."
    }
  ],

  "characters": [
    {
      "id": 1,
      "name": "Kira Ashvane",
      "player_name": "Sam",
      "class": "Rogue (Arcane Trickster)",
      "level": 7,
      "hp_max": 45,
      "armor_class": 16,
      "good_evil_score": 3,
      "law_chaos_score": -4,
      "alignment_label": "Chaotic Good"
    },
    {
      "id": 2,
      "name": "Thorne Valdris",
      "player_name": "Jamie",
      "class": "Paladin (Oath of Devotion)",
      "level": 7,
      "hp_max": 68,
      "armor_class": 20,
      "good_evil_score": 7,
      "law_chaos_score": 6,
      "alignment_label": "Lawful Good"
    },
    {
      "id": 3,
      "name": "Lyra Voss",
      "player_name": "Alex",
      "class": "Wizard (School of Divination)",
      "level": 7,
      "hp_max": 38,
      "armor_class": 13,
      "good_evil_score": 1,
      "law_chaos_score": 2,
      "alignment_label": "Lawful Neutral"
    }
  ],

  "session": {
    "id": 4,
    "title": "The Crimson Vow",
    "session_number": 4,
    "type": "sequential",
    "status": "prepared",
    "setup_text": "Last session, the party recovered the second Covenant Shard from the Ashen Parliament's vault. But Thorne felt the shard's corruption — a voice whispering that the Dawnkeepers' cause is futile. The party now arrives at Millhaven, where rumors say a Hollow Court agent is turning the town.",
    "scenes": [
      {
        "title": "Arrival at Millhaven",
        "description": "The town is quiet. Too quiet. Half the market stalls are shuttered. A child stares with hollow eyes.",
        "sort_order": 1
      },
      {
        "title": "The Undercroft",
        "description": "Beneath the temple: a Hollow Court shrine. The town priest, Father Aldric, has been turned.",
        "sort_order": 2
      },
      {
        "title": "The Crimson Choice",
        "description": "Father Aldric can be saved — but only by using the Covenant Shard, which will advance the Twilight Clock by 1.",
        "sort_order": 3
      }
    ],
    "encounters": [
      {
        "name": "Hollow Court Thralls",
        "scene_index": 1,
        "difficulty": "medium",
        "monsters": [
          { "name": "Hollow Thrall", "hp_max": 22, "armor_class": 13, "count": 4 },
          { "name": "Shadow Wight", "hp_max": 45, "armor_class": 14, "count": 1 }
        ]
      }
    ],
    "branch_options": [
      {
        "label": "Option A: Use the Shard to save Father Aldric",
        "description": "Sacrifice divine power to save one man. The shard dims. The twilight advances.",
        "consequences": [
          { "type": "immediate", "description": "Father Aldric is restored. The shard's glow weakens. Twilight Clock +1." },
          { "type": "delayed", "description": "Aldric becomes a devoted ally. The Dawnkeepers question why the party 'wasted' the shard." },
          { "type": "meta", "description": "Establishes the party values individual life over strategic advantage." }
        ]
      },
      {
        "label": "Option B: Destroy Father Aldric to preserve the Shard",
        "description": "A mercy kill. The shard stays strong. The town mourns.",
        "consequences": [
          { "type": "immediate", "description": "Aldric dies at peace. The shard remains at full power." },
          { "type": "delayed", "description": "Millhaven resents the party. The Dawnkeepers praise their pragmatism." },
          { "type": "meta", "description": "Establishes the party will make hard sacrifices for the greater mission." }
        ]
      },
      {
        "label": "Option C: Search for an alternative cure",
        "description": "Lyra's divination might find another way — but it costs time, and the twilight doesn't wait.",
        "consequences": [
          { "type": "immediate", "description": "Requires 2 successful Arcana checks (DC 18). Failure: Aldric turns fully and attacks." },
          { "type": "delayed", "description": "If successful: party discovers Hollow Court corruption can be reversed without shards — game changer." },
          { "type": "meta", "description": "Opens a new quest line: finding the counter-ritual. High risk, highest reward." }
        ]
      }
    ]
  }
}
```

---

## Appendix: Quick Start Checklist

```bash
# 1. Create project
laravel new dm-forge --livewire
cd dm-forge

# 2. Configure SQLite
# .env: DB_CONNECTION=sqlite

# 3. Install AI SDK
composer require laravel/ai
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"

# 4. Set AI keys
# .env: ANTHROPIC_API_KEY=sk-ant-...

# 5. Run migrations
php artisan migrate

# 6. Create agents
php artisan make:agent AlignmentAdvisor --structured
php artisan make:agent SessionOutliner
php artisan make:agent ConsequenceGenerator --structured
php artisan make:agent NarrativeWriter
php artisan make:agent TagClassifier --structured

# 7. Create tools
php artisan make:tool LookupNpc
php artisan make:tool LookupLocation
php artisan make:tool GetSessionLogs
php artisan make:tool RollDice

# 8. Start building Livewire components
php artisan make:livewire Dashboard
php artisan make:livewire Campaigns/CampaignCreate
# ... etc.
```
