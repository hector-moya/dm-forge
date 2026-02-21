<?php

namespace App\Ai\Agents;

use App\Ai\Concerns\HasCampaignContext;
use App\Ai\Tools\LookupFaction;
use App\Ai\Tools\LookupLocation;
use App\Ai\Tools\LookupNpc;
use App\Ai\Tools\LookupSrdMonster;
use App\Models\Campaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
#[Temperature(0.8)]
class SessionGenerator implements Agent, HasStructuredOutput, HasTools
{
    use Promptable, HasCampaignContext;

    public function __construct(
        protected Campaign $campaign,
    ) {}

    public function instructions(): Stringable|string
    {
        $context = $this->buildCampaignContext();

        return <<<PROMPT
You are an expert D&D session designer. Generate a complete, ready-to-run game session with scenes, encounters, NPCs, monsters, branching paths, and puzzles.

{$context}

Guidelines:
- Create 3-5 scenes that form a cohesive narrative arc with a clear beginning, rising action, and climax
- Each scene should have vivid descriptions the DM can read aloud or paraphrase
- Include combat encounters with specific SRD monsters — use the lookup tool to find appropriate monsters by type and challenge rating
- Reference existing campaign NPCs, factions, and locations using the lookup tools
- Include scene notes mentioning which campaign NPCs appear and their role in the scene
- Add meaningful branch options that give players real choices with different consequences
- Include puzzles where they fit naturally (riddles at ancient doors, logic puzzles in wizard towers, etc.) — not every scene needs one
- The setup_text should be atmospheric read-aloud text to set the stage for players
- DM notes should include tips, secret information, and contingency plans
- Monster quantities should be appropriate for a party of 4-5 adventurers
- Vary encounter difficulty across scenes (not every fight should be hard)
- Branch options should present genuine dilemmas, not obvious good/bad choices
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new LookupNpc($this->campaign),
            new LookupFaction($this->campaign),
            new LookupLocation($this->campaign),
            new LookupSrdMonster,
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required()
                ->description('Session title — evocative and specific (e.g., "The Siege of Ashenmoor")'),
            'setup_text' => $schema->string()->required()
                ->description('Read-aloud text to set the scene for players at the start of the session (2-3 paragraphs)'),
            'dm_notes' => $schema->string()->required()
                ->description('Private DM notes: key secrets, contingency plans, and session goals'),
            'scenes' => $schema->array()->items(
                $schema->object([
                    'title' => $schema->string()->required()
                        ->description('Scene title'),
                    'description' => $schema->string()->required()
                        ->description('Scene description — what happens, what players see and experience'),
                    'notes' => $schema->string()->required()
                        ->description('DM-only notes: NPC motivations, hidden details, which campaign NPCs appear and their role'),
                    'encounters' => $schema->array()->items(
                        $schema->object([
                            'name' => $schema->string()->required()
                                ->description('Encounter name (e.g., "Ambush at the Bridge")'),
                            'description' => $schema->string()->required()
                                ->description('Encounter setup and tactical details'),
                            'environment' => $schema->string()->required()
                                ->description('Combat environment (e.g., "narrow stone bridge over a chasm")'),
                            'difficulty' => $schema->string()->enum(['easy', 'medium', 'hard'])->required()
                                ->description('Encounter difficulty'),
                            'monsters' => $schema->array()->items(
                                $schema->object([
                                    'name' => $schema->string()->required()
                                        ->description('Exact SRD monster name (e.g., "Goblin", "Young Red Dragon")'),
                                    'quantity' => $schema->integer()->min(1)->max(20)->required()
                                        ->description('Number of this monster type'),
                                ])
                            )->required()->description('Monsters in this encounter — use exact SRD names from the lookup tool'),
                        ])
                    )->description('Combat encounters in this scene (can be empty if scene is social/exploration)'),
                    'branch_options' => $schema->array()->items(
                        $schema->object([
                            'label' => $schema->string()->required()
                                ->description('Short label for this choice (e.g., "Side with the rebels")'),
                            'description' => $schema->string()->required()
                                ->description('What this choice entails and its potential consequences'),
                        ])
                    )->description('Decision points for players in this scene'),
                    'puzzle' => $schema->object([
                        'name' => $schema->string()->required()
                            ->description('Puzzle name'),
                        'description' => $schema->string()->required()
                            ->description('The puzzle as presented to players'),
                        'solution' => $schema->string()->required()
                            ->description('The solution to the puzzle'),
                        'hint_tier_1' => $schema->string()->required()
                            ->description('Subtle hint — a minor clue for observant players'),
                        'hint_tier_2' => $schema->string()->required()
                            ->description('Moderate hint — more direct nudge toward the solution'),
                        'hint_tier_3' => $schema->string()->required()
                            ->description('Obvious hint — nearly gives away the answer'),
                        'difficulty' => $schema->string()->enum(['easy', 'medium', 'hard'])->required()
                            ->description('Puzzle difficulty'),
                        'puzzle_type' => $schema->string()->enum(['riddle', 'logic', 'physical', 'cipher', 'pattern'])->required()
                            ->description('Type of puzzle'),
                    ])->description('Optional puzzle in this scene — omit if no puzzle fits naturally'),
                ])
            )->required()->min(3)->max(5)->description('3-5 scenes forming a narrative arc'),
        ];
    }
}
