# Contao contact profiles

## Requirements

## Configuration

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
    fields:
      - 'salutation'
      - 'profession'  
```
