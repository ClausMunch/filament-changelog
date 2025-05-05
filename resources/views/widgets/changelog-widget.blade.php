{{-- resources/views/widgets/changelog-widget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        @if(isset($data['error']))
            <div class="text-danger-600 dark:text-danger-400 p-4">
                <p><strong>Error loading changelog:</strong> {{ $data['error'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Please check your `config/filament-changelog.php` file, `.env` variables (CHANGELOG_GITHUB_REPOSITORY, CHANGELOG_GITHUB_TOKEN), and ensure the GitHub token has the correct permissions.
                </p>
            </div>
        @elseif(isset($data['releases']) && !empty($data['releases']))
            <div class="space-y-6">
                @foreach($data['releases'] as $release)
                    <div class="border-b border-gray-200 dark:border-white/10 pb-4 last:border-b-0 last:pb-0">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <a href="{{ $release['html_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="hover:underline">
                                    {{ $release['name'] ?: $release['tag_name'] }}
                                </a>
                                @if($release['name'] && $release['tag_name'])
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 ml-2">({{ $release['tag_name'] }})</span>
                                @endif
                            </h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $this->formatDate($release['published_at'] ?? null) }}
                                @if($release['prerelease'] ?? false)
                                    <span class="ml-2 inline-flex items-center rounded-full bg-warning-100 px-2 py-0.5 text-xs font-medium text-warning-800 dark:bg-warning-500/10 dark:text-warning-400">
                                        Pre-release
                                    </span>
                                @endif
                            </span>
                        </div>

                        @if(!empty($release['body']))
                            <div @class([
                                'prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300',
                                // Add more prose modifiers if needed: e.g., prose-indigo
                            ])>
                                {!! $this->parseMarkdown($release['body']) !!}
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">No description provided for this release.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
             <p class="text-gray-500 dark:text-gray-400 p-4 text-center">No releases found or unable to fetch data.</p>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>