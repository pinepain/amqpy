[![Build Status](https://travis-ci.org/pinepain/amqpy.png?branch=master)](https://travis-ci.org/zaq178miami/amqpy)

AMQPy
=====

Lightweight AMQP framework built on top of the [php-amqp](https://github.com/pdezwart/php-amqp) extension.

Requirementes
-------------

 * [php-amqp](http://pecl.php.net/package/amqp) >= 1.4.0 (for now master branch only) (with [librabbitmq]() >= 0.4.1)

It is aimed to provide painless as much as possible AMQP integration in your applications.

You can find some examples in `/demo` folder but they are incomplete and non-documented now (and probably doesn't work now),
sorry guys.

If you have questions of feature request or even found a bug (wow! you are really awesome) please create an issue
or even send pull request or old-style patch.

*NOTE* demos are completely outdated

TODO
====

- [ ] - update demo
- [ ] - update readme
- [ ] - add some docs
- [ ] - laravel bundle
- [ ] - code review

- [x] - don't forget to change travis image back to master [![Build Status](https://travis-ci.org/pinepain/amqpy.png?branch=master)](https://travis-ci.org/zaq178miami/amqpy)
- [x] - unit tests
- [x] - code coverage
- [x] - C.R.A.P. index review
- [x] - finish redesign
- [x] - add getChannel() and getConnection() methods to AMQPChannel, AMQPQueue and AMQPQueue
- [x] - drop solutions support while they are too situation-specific
- [x] - drop php < 5.5 support to use new features (final)
