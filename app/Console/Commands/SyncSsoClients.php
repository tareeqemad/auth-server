<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncSsoClients extends Command
{
    protected $signature = 'sso:sync-clients
                            {--rotate-secrets : Regenerate secrets for existing clients}';

    protected $description = 'Create or update OAuth clients for the registered SSO systems (idempotent).';

    public function handle(): int
    {
        $created = [];
        $updated = [];

        foreach (config('sso_clients') as $definition) {
            $client = Application::where('name', $definition['name'])->first();

            if ($client === null) {
                $plainSecret = Str::random(40);

                $client = Application::create([
                    'name' => $definition['name'],
                    'slug' => $definition['key'],
                    'display_name_ar' => $definition['display_name'] ?? null,
                    'description' => $definition['description'] ?? null,
                    'color' => $definition['color'] ?? '#475569',
                    'launch_url' => $definition['launch_url'] ?? null,
                    'secret' => $definition['confidential'] ? Hash::make($plainSecret) : null,
                    'redirect_uris' => $definition['redirect_uris'],
                    'grant_types' => $definition['grant_types'],
                    'provider' => 'users',
                    'revoked' => false,
                    'is_first_party' => true,
                ]);

                $created[] = [
                    'key' => $definition['key'],
                    'name' => $definition['name'],
                    'client_id' => $client->id,
                    'client_secret' => $plainSecret,
                    'redirect_uris' => $definition['redirect_uris'],
                ];

                continue;
            }

            $client->redirect_uris = $definition['redirect_uris'];
            $client->grant_types = $definition['grant_types'];
            $client->revoked = false;
            $client->slug = $client->slug ?: $definition['key'];
            $client->display_name_ar = $client->display_name_ar ?: ($definition['display_name'] ?? null);
            $client->description = $client->description ?: ($definition['description'] ?? null);
            $client->color = $client->color ?: ($definition['color'] ?? '#475569');
            $client->launch_url = $client->launch_url ?: ($definition['launch_url'] ?? null);

            $rotatedSecret = null;

            if ($this->option('rotate-secrets') && $definition['confidential']) {
                $rotatedSecret = Str::random(40);
                $client->secret = Hash::make($rotatedSecret);
            }

            $client->save();

            $updated[] = [
                'key' => $definition['key'],
                'name' => $definition['name'],
                'client_id' => $client->id,
                'rotated_secret' => $rotatedSecret,
            ];
        }

        $this->renderResults($created, $updated);

        return self::SUCCESS;
    }

    private function renderResults(array $created, array $updated): void
    {
        if ($created !== []) {
            $this->newLine();
            $this->components->info('Created '.count($created).' new SSO client(s):');

            foreach ($created as $c) {
                $this->line("  <fg=cyan>[{$c['key']}]</> {$c['name']}");
                $this->line("     client_id:     <fg=yellow>{$c['client_id']}</>");
                $this->line("     client_secret: <fg=yellow>{$c['client_secret']}</>   (shown only once!)");
                $this->line('     redirect_uris: '.implode(', ', $c['redirect_uris']));
                $this->newLine();
            }

            $this->components->warn('Save the client_secret values in a password manager — they cannot be recovered.');
        }

        if ($updated !== []) {
            $this->newLine();
            $this->components->info('Updated '.count($updated).' existing SSO client(s):');

            foreach ($updated as $u) {
                $line = "  <fg=cyan>[{$u['key']}]</> {$u['name']} ({$u['client_id']})";
                if ($u['rotated_secret'] !== null) {
                    $line .= " — <fg=red>SECRET ROTATED:</> <fg=yellow>{$u['rotated_secret']}</>";
                }
                $this->line($line);
            }
        }

        if ($created === [] && $updated === []) {
            $this->components->info('No SSO clients defined in config/sso_clients.php.');
        }
    }
}
