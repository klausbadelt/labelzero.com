require 'rest_client'
require 'json'
require 'pp'

API_KEY = 'razitillagvafudrmannfornoldr'
response = RestClient.get "http://api.bandcamp.com/api/band/3/discography?key=#{API_KEY}&band_id=1477992086,2983633648,2989315057,3310037202"
#response = RestClient.get "http://api.bandcamp.com/api/band/3/discography?key=#{API_KEY}&band_id=3310037202,1477992086"
bands = JSON.parse response
puts "HTTP #{response.code}"
puts "Received #{bands.size} bands"
bands.each do |id, band|
  band['discography'].each do |album|
    postname = File.join(File.expand_path(File.dirname(__FILE__)), "../_posts/#{Time.at(album['release_date']).strftime("%Y-%m-%d")}-#{album['title'].gsub( /[^a-z0-9\-]+/i, '_')}.md")
    next if File.exists?(postname)
    puts "Artist: #{album['artist']} | Album: #{album['title']}"
    album_response = RestClient.get "http://api.bandcamp.com/api/album/2/info",
      { params: { key: API_KEY, album_id: album['album_id'] } }
    album_info = JSON.parse album_response
    uri = URI album['url']
    File.open(postname, 'w') do |post|
      post.write(<<eos)
---
layout: post
title: "#{album['title']}"
artist: "#{album['artist']}"
album_id: "#{album['album_id']}"
small_art_url: "#{album['small_art_url']}"
large_art_url: "#{album['large_art_url']}"
full_art_url: "#{album['large_art_url'].sub(/_2\.jpg/,'_10.jpg')}"
bc_url: "#{album['url']}"
permalink: "#{uri.path}"
---
eos
     post.write "####About\n#{album_info['about'].gsub(/\r/,"  ")}\n\n" unless album_info['about'].nil?
     post.write "####Credits\n#{album_info['credits'].gsub(/\r/,"  ")}\n\n" unless album_info['credits'].nil?
    end
  end
end
