# laminas-di

[![Build Status](https://github.com/laminas/laminas-di/workflows/continuous-integration.yml/badge.svg)](https://github.com/laminas/laminas-di/actions/workflows/continuous-integration.yml)

laminas-di provides autowiring to implement Inversion of Control (IoC) containers.
IoC containers are widely used to create object instances that have all
dependencies resolved and injected. Dependency Injection containers are one form
of IoC – but not the only form.

laminas-di is designed to be simple, fast and reusable. It provides the following features:

- Constructor injection
- Autowiring:
  - Recursively through all dependencies
  - With configured type preferences
  - with configured injections
  - With injections passed in the create() call
- Code generators to create factories usable by other IoC containers like Laminas\ServiceManager

It does __not__ provide:

- Setter, interface, property or any other injection method than constructor injection
- Support for factories
- Declaring shared/unshared instances
  - the injector always creates new instances
  - the default container always shares instances
- Support for variadic arguments in __construct

If you need these features combine it with another IoC container such as
[laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager/).

- File issues at https://github.com/laminas/laminas-di/issues
- Documentation is at https://docs.laminas.dev/laminas-di/
