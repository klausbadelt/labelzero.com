require 'rest_client'
require 'json'
require 'pp'

response = RestClient.get 'http://api.bandcamp.com/api/band/3/discography?key=razitillagvafudrmannfornoldr&band_id=1477992086,2983633648,2989315057,3310037202' # params don't work with commas :-(
bands = JSON.parse response
puts "HTTP #{response.code}"
puts "Received #{bands.size} bands"
bands.each do |id, band|
  band['discography'].each do |album|
    puts "Artist: #{album['artist']}  Album: #{album['title']}"
    # => album['artist] => Artist
    # => album['title] => Title
    
    postname = File.join(File.expand_path(File.dirname(__FILE__)), "../_posts/#{Time.at(album['release_date']).strftime("%Y-%m-%d")}-#{album['title'].gsub(/ /, '-')}.md")
    puts "Writing #{postname}"
    File.open(postname, 'w') do |post|
      post.write(<<eos)
---
layout: post
title: "#{album['title']}"
cover: "#{album['large_art_url']}"
store: "#{album['url']}"
---
eos
    end
  end
end
