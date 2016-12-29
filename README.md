# MUGicalPHP
PHP script to scrape Meetup Group events and generate an iCal feed

**Goal:** Create an iCal feed of events for tech-related meetup groups in a given city.

Starting point was [this handy PHP library](https://github.com/user3581488/Meetup) for interacting with the [Meetup API](https://www.meetup.com/meetup_api/). 

There are various ways to authenticate with the Meetup API, but I've just gone with using the basic API key. (I'm only reading, and I'm only interested in public information anyway.)

I'm currently pulling in groups that cover the following topics:

* 48471 - computer programming
* 17628 - programming languages
* 15582 - web development
* 3833 - software development
* 84681 - cryptography
* 79740 - internet of things

Still to do:

* Allow city to be configurable (currently it's hard-coded to Sydney, Australia)
* Implement a blacklist of groups that we don't want to include
* Figure out how to include groups that aren't on Meetup.com