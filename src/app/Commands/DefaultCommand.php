<?php

namespace App\Commands;

use Fgilio\AgentSkillFoundation\Router\ParsedInput;
use Fgilio\AgentSkillFoundation\Router\Router;
use LaravelZero\Framework\Commands\Command;

/**
 * Main command router.
 *
 * Routes incoming arguments to appropriate handlers.
 */
class DefaultCommand extends Command
{
    protected $signature = 'default {args?*}';

    protected $description = 'Main command router';

    protected $hidden = true;

    public function __construct(private Router $router)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->ignoreValidationErrors();
    }

    public function handle(): int
    {
        return $this->router
            ->routes($this->routes())
            ->help(fn (Command $ctx, ?string $cmd = null) => $this->showHelp($cmd))
            ->unknown(fn (ParsedInput $p, Command $ctx) => $this->unknownCommand($p))
            ->run($this);
    }

    private function routes(): array
    {
        return [
            'docs' => fn (ParsedInput $p, Command $ctx) => $this->call('docs', [
                '--category' => $p->scanOption('category'),
                '--json' => $p->wantsJson(),
            ]),
            'search' => fn (ParsedInput $p, Command $ctx) => $this->routeSearch($p),
            'show' => fn (ParsedInput $p, Command $ctx) => $this->routeShow($p),
            'discover' => fn (ParsedInput $p, Command $ctx) => $this->call('discover', [
                '--json' => $p->wantsJson(),
            ]),
            'usages' => fn (ParsedInput $p, Command $ctx) => $this->routeUsages($p),
            'update' => fn (ParsedInput $p, Command $ctx) => $this->call('update', [
                '--item' => $p->scanOption('item'),
                '--category' => $p->scanOption('category'),
                '--delay' => $p->scanOption('delay', null, 500),
                '--dry-run' => $p->hasFlag('dry-run'),
            ]),
            'build' => fn (ParsedInput $p, Command $ctx) => $this->call('build', [
                '--no-install' => $p->hasFlag('no-install'),
            ]),
        ];
    }

    private function routeSearch(ParsedInput $p): int
    {
        if ($p->wantsHelp()) {
            return $this->showCommandHelp('search', 'search <query> [--limit=N] [--json]', 'Fuzzy search documentation');
        }

        $query = $p->arg(0);
        if (empty($query)) {
            $this->error('Search query required');
            return self::FAILURE;
        }

        return $this->call('search', [
            'query' => $query,
            '--limit' => $p->scanOption('limit', 'l', 10),
            '--json' => $p->wantsJson(),
        ]);
    }

    private function routeShow(ParsedInput $p): int
    {
        if ($p->wantsHelp()) {
            return $this->showCommandHelp('show', 'show <name> [--section=NAME] [--json]', 'Show documentation for a Flux UI item');
        }

        $name = $p->arg(0);
        if (empty($name)) {
            $this->error('Item name required');
            return self::FAILURE;
        }

        return $this->call('show', [
            'name' => $name,
            '--section' => $p->scanOption('section', 's'),
            '--json' => $p->wantsJson(),
        ]);
    }

    private function routeUsages(ParsedInput $p): int
    {
        if ($p->wantsHelp()) {
            return $this->showCommandHelp('usages', 'usages <component> [--json]', 'Show where a component is used');
        }

        $component = $p->arg(0);
        if (empty($component)) {
            $this->error('Component name required');
            return self::FAILURE;
        }

        return $this->call('usages', [
            'component' => $component,
            '--json' => $p->wantsJson(),
        ]);
    }

    private function unknownCommand(ParsedInput $p): int
    {
        $subcommand = $p->subcommand();

        if ($p->wantsJson()) {
            fwrite(STDERR, json_encode([
                'error' => "Unknown command: {$subcommand}",
                'valid_commands' => $this->router->routeNames(),
            ], JSON_PRETTY_PRINT) . "\n");
        } else {
            $this->error("Unknown command: {$subcommand}");
            $this->line('');
            $this->line('Run with --help for usage.');
        }

        return self::FAILURE;
    }

    private function showCommandHelp(string $command, string $usage, string $description): int
    {
        $name = config('app.name');

        $this->info($description);
        $this->line('');
        $this->line('Usage:');
        $this->line("  {$name} {$usage}");
        $this->line('');

        return self::SUCCESS;
    }

    private function showHelp(?string $subcommand = null): int
    {
        $name = config('app.name');

        $this->line("{$name} - Offline Flux UI documentation");
        $this->line('');
        $this->line('Usage:');
        $this->line("  {$name} <command> [options]");
        $this->line('');
        $this->line('Commands:');
        $this->line('  docs [--category=] [--json]          List all documentation items');
        $this->line('  search <query> [--limit=] [--json]   Fuzzy search documentation');
        $this->line('  show <name> [--section=] [--json]    Show full documentation');
        $this->line('  discover [--json]                    Find undocumented components');
        $this->line('  usages <component> [--json]          Show where component is used');
        $this->line('  update [--item=] [--delay=]          Scrape latest docs');
        $this->line('');
        $this->line('Categories: components, layouts, guides');
        $this->line('');
        $this->line('Examples:');
        $this->line("  {$name} docs --category=components");
        $this->line("  {$name} search modal");
        $this->line("  {$name} show button");
        $this->line("  {$name} usages subheading");
        $this->line('');

        return self::SUCCESS;
    }
}
