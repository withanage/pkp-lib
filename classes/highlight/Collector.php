<?php
/**
 * @file classes/highlight/Collector.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2000-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Collector
 *
 * @brief A helper class to configure a Query Builder to get a collection of highlights
 */

namespace PKP\highlight;

use APP\facades\Repo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use PKP\core\interfaces\CollectorInterface;
use PKP\plugins\Hook;

/**
 * @template T of Highlight
 */
class Collector implements CollectorInterface
{
    public const SITE_ONLY = 'site';
    public const SITE_AND_CONTEXTS = 'all';

    public DAO $dao;
    public ?array $contextIds = null;
    public ?string $includeSite = null;
    public ?int $count = null;
    public ?int $offset = null;

    public function __construct(DAO $dao)
    {
        $this->dao = $dao;
    }

    /** @copydoc DAO::getCount() */
    public function getCount(): int
    {
        return $this->dao->getCount($this);
    }

    /**
     * @copydoc DAO::getIds()
     * @return Collection<int,int>
     */
    public function getIds(): Collection
    {
        return $this->dao->getIds($this);
    }

    /**
     * @copydoc DAO::getMany()
     * @return LazyCollection<int,T>
     */
    public function getMany(): LazyCollection
    {
        return $this->dao->getMany($this);
    }

    /**
     * @copydoc Repository::deleteMany()
     */
    public function deleteMany(): void
    {
        Repo::highlight()->deleteMany($this);
    }

    /**
     * Filter highlights by one or more contexts
     */
    public function filterByContextIds(?array $contextIds): self
    {
        $this->contextIds = $contextIds;
        return $this;
    }

    /**
     * Include site-level highlights in the collection
     */
    public function withSiteHighlights(?string $includeMethod = self::SITE_AND_CONTEXTS): self
    {
        $this->includeSite = $includeMethod;
        return $this;
    }

    /**
     * Limit the number of objects retrieved
     */
    public function limit(?int $count): self
    {
        $this->count = $count;
        return $this;
    }

    /**
     * Offset the number of objects retrieved, for example to
     * retrieve the second page of contents
     */
    public function offset(?int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function getQueryBuilder(): Builder
    {
        $qb = DB::table($this->dao->table . ' as h')
            ->select(['h.*'])
            ->when(isset($this->contextIds) && $this->includeSite !== self::SITE_ONLY, function($qb) {
                $qb->whereIn('h.' . $this->dao->parentKeyColumn, $this->contextIds);
                if ($this->includeSite === self::SITE_AND_CONTEXTS) {
                    $qb->orWhereNull('h.' . $this->dao->parentKeyColumn);
                }
            }, function($qb) {
                 if ($this->includeSite === self::SITE_ONLY) {
                    $qb->whereNull('h.' . $this->dao->parentKeyColumn);
                }
            })
            ->when(isset($this->count), function($qb) {
                $qb->limit($this->count);
            })
            ->when(isset($this->offset), function($qb) {
                $qb->offset($this->offset);
            })
            ->orderBy('h.sequence', 'asc');

        Hook::run('Highlight::Collector', [&$qb, $this]);

        return $qb;
    }
}
