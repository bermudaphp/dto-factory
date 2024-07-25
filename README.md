# Install
```bash
composer require bermudaphp/dto-factory
```

# Usage
```php
readonly class CreatePostData
{
    public function __construct(
        public string                          $title,
        #[From('title', true)]
        #[Cast(Slugify::class)]
        public string                          $slug,
        #[Cast('json')]
        public array                           $content,
        public UserReference|User              $author,
        #[From('categoryId')]
        #[Cast([CategoryReference::class, 'fromId'])]
        public null|CategoryReference|Category $category,
        #[Cast([TagsCollection::class, 'fromJsonString'])]
        #[Defaults(null)]
        public readonly ?iterable $tags,
        #[Cast('json')]
        public ?array                          $metadata,
        #[Cast(Boolean::class)]
        #[Defaults(true)]
        public bool                            $commentable,
        #[Defaults(false)]
        public bool                            $pinned,
        #[Invoke('now')]
        public CarbonInterface                 $createdAt,
        #[Cast([Clock::class, 'create'])]
        public ?CarbonInterface                $publishedAt = null,
        public ?CarbonInterface                $updatedAt = null,
        public ?CarbonInterface                $deletedAt = null,
    ) {
    }
}

$factory = new ObjectFactory;

$dto = factory->make(CreatePostData::class, [
  'title' => 'My First Post',
  'content' => 'valid json string',
  'author' => new User(1),
  'categoryId' => 2
], true)
```
