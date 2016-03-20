# fair-web-analytics

## Why?

I used Piwik to track visit statistics of my blog. It's a great tool, but over the time I recognised, that through this way I store much more data then I want. 

I am an enemy of data retention and especially if the user can't see the data hold about him. I am not interested that anyone knows which specific amount of seconds i spent to read an article. 

So why should i collect this data from others? So what to do? Collect less and trade it fair.

## The Concept

You can enable the tracking for several domains. You can add tracking pixels on every Site of the domain. 

If the site with the pixel is called, the tracking server will store the date (year-month-day) and a hash based on date, IP and an additional salt. A Track of a site with the same ip will just once a day stored. The rest of the requests will be ignored. On the next day the same IP will generate another hash, so no one can make a connection to a user on a former day. For visits with the same IP on the same day the hash can be linked. 

The statistic can be queried by anyone over a REST API or can be displayed with a javascript graph lib

## Installation

<pre><code> cp installation/.ini config/
</code></pre>
read through the *.ini files in config and modify them

## TODOS
* Demo Link
* Code Documentation
* API Documentation
* javascript lib Documentation
* most used API calls
* visits vs users graphs
