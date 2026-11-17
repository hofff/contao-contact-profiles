# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.1] - 2026-11-17

### Fixed

- Keep all positions of the image size configuration. Empty values were removed, so a predefined image size
  (e.g. `['', '', '55']`) was used as image width ([43879ea]).
- Do not filter empty values from configured profile, category and source lists, only cast them to the expected type
  ([43879ea]). Both regressions were introduced in 2.0.0 by [a3d63e3].

[Unreleased]: https://github.com/hofff/contao-contact-profiles/compare/2.0.1...HEAD
[2.0.1]: https://github.com/hofff/contao-contact-profiles/compare/2.0.0...2.0.1
[43879ea]: https://github.com/hofff/contao-contact-profiles/commit/43879ea
[a3d63e3]: https://github.com/hofff/contao-contact-profiles/commit/a3d63e3
