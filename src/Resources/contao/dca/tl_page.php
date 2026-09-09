<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_page']['palettes']['contact_profile'] = '{title_legend},title,type'
    . ';{routing_legend},alias,routePath,routePriority,routeConflicts'
    . ';{meta_legend},pageTitle,robots,description,serpPreview'
    . ';{canonical_legend:hide},canonicalLink,canonicalKeepParams'
    . ';{protected_legend:hide},protected'
    . ';{layout_legend:hide},includeLayout'
    . ';{cache_legend:hide},includeCache'
    . ';{chmod_legend:hide},includeChmod'
    . ';{expert_legend:hide},cssClass,sitemap,hide,noSearch,guests'
    . ';{tabnav_legend:hide},tabindex,accesskey'
    . ';{publish_legend},published,start,stop';
