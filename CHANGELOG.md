# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial SDK architecture
- Core contracts/interfaces (CourierInterface, ShipmentInterface, TrackingInterface, RateInterface, CodInterface, WebhookInterface, MetadataInterface)
- Core management classes (CourierManager, CourierRegistry, CourierResolver, StatusMapper, CapabilityDetector)
- DTO classes (Shipment, Tracking, Rate, Cod)
- Support classes (HttpClient, ConfigHelper)
- Exception classes (CourierException, ApiException, InvalidConfigurationException, UnsupportedCapabilityException)
- Pathao courier driver implementation
- Facade class (DeshCourier)
- Comprehensive documentation
- Usage examples for plain PHP, Laravel, and WordPress

### Features
- Multi-courier support with unified interface
- Capability detection and graceful degradation
- Status normalization across all couriers
- Framework-agnostic design (works with plain PHP, Laravel, WordPress)
- Configuration support via .env, Laravel config, WordPress constants
- Webhook support
- COD tracking and settlement
- Rate estimation
- Bulk operations support

---

## [0.1.0] - 2024-01-XX

### Added
- Initial release
