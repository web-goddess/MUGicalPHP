# MUGicalPHP
PHP script to scrape Meetup Group events and generate an iCal feed

**Goal:** Create an iCal feed of events for tech-related meetup groups in a given city.

**Result:** Feeds for [Sydney](http://www.krishoward.org/sydneymugs.ics), [Melbourne](http://www.krishoward.org/melbournemugs.ics), [Brisbane](http://www.krishoward.org/brisbanemugs.ics), [Perth](http://www.krishoward.org/perthmugs.ics), and [Hobart](http://www.krishoward.org/hobartmugs.ics). These get updated once a day, so feel free to subscribe in your calendar application of choice! (Note: I make no guarantees that the event details are up-to-date. You should always click through to the relevant website to check before heading out.)

----

My starting point was [this handy PHP library](https://github.com/user3581488/Meetup) for interacting with the [Meetup API](https://www.meetup.com/meetup_api/). 

There are various ways to authenticate with the Meetup API, but I've just gone with using the basic API key. (I'm only reading, and I'm only interested in public information anyway.)

I'm currently pulling in groups that cover the following topics:

* 48471 - computer programming
* 17628 - programming languages
* 15582 - web development
* 3833 - software development
* 84681 - cryptography
* 79740 - internet of things
* 21549 - agile project management
* 21441 - mobile development
* 18062 - big data
* 15167 - cloud computing
* 124668 - computer security
* 10209 - web technology

The default city is Sydney, Australia. You can choose Melbourne, Brisbane, Perth, or Hobart by appending "?location=melbourne" etc. to the URL.

Still to do:

* <strike>Generate static .ics file</strike> - Done!
* <strike>Allow city to be configurable (currently it's hard-coded to Sydney, Australia)</strike>
* Implement a blacklist of groups that we don't want to include
* Figure out how to include groups that aren't on Meetup.com