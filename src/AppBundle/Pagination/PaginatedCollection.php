<?php

namespace AppBundle\Pagination;

class PaginatedCollection
{
    private $items;

    private $total;

    private $count;

    private $_links = array();

    public function __construct(array $items, $totalItems)
    {
        $this->items = $items;
        $this->total = $totalItems;
        $this->count = count($items);
    }

    public function addLink($ref, $url) // Allows us to add links. Passing it $ref which referes to the name of the link e.g. "first" or "last" and $url
    {
        $this->_links[$ref] = $url;
    }
}
