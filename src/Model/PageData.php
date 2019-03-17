<?php

namespace BLHylton\InfoResStoreLocator\Model;


class PageData
{
    /** @var array $data Collection of the data rows retrieved */
    public $data;
    /** @var bool $morePages Is there another page after this one? */
    public $morePages;
    /** @var int $currentPageNumber Current page number */
    public $currentPageNumber;
}