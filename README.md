# Contao Contact profiles

This extensions provides a feature rich contact profile integration into Contao. It integrates into several bundles.

 - [ContaoNewsBundle](https://github.com/contao/news-bundle)
 - [ContaoCalendarBundle](https://github.com/contao/calendar-bundle)
 - [ContaoFaqBundle](https://github.com/contao/faq-bundle)
 - [CodefogNewsCategoriesBundle](https://github.com/codefog/contao-news_categories)

It's also works together with:

 - [HofffContaoSocialTagsBundle](https://github.com/hofff/contao-social-tags/): Automatic generation of opengraph and twitter card tags
 - [HofffContaoConsentBridgeBundle](https://github.com/hofff/contao-consent-bridge): Consent integration for YouTube and Vimeo videos

## Requirements

 - Contao `^4.9`
 - PHP `^7.4 || ^8.0`

## Features

 - Contact profile list module/content element (custom profiles, related profiles, by category)
 - Contact profile detail module/content element
 - Initials filter module/content element
 - Related news categories module

## Multilingual support

This extension has a built-in support for multilingual contact profiles. To enable it you have to install
[terminal42/dc_multilingual](https://github.com/terminal42/contao-DC_Multilingual) and adjust the bundle configuration:

```yaml

hofff_contact_profiles:
  multilingual:
    # Enable the translation
    enable: true
    # Optional, otherwise the languages of the root pages are used
    languages: ['de', 'en']
    # Optional, otherwise a special fallback language is used
    fallbackLanguage: 'en'
    # Overrides the translated fields of a contact profile
    # By default following fields are translatable:
    fields:
      - 'alias'
      - 'salutation'
      - 'title'
      - 'position'
      - 'profession'
      - 'caption'
      - 'websiteTitle'
      - 'teaser'
      - 'description'
      - 'statement'
      - 'jumpTo'
      - 'videos'
```

After adjusting the configuration you have to run `contao:migrate`.

**Warning** Disabling multilingual support after having created translated profiles will leads to incomplete and
duplicated entries!
