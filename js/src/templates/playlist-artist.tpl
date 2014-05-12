<% if (playlist.artist_url) { %>
<span class="playlist-artist"><a href="<%= playlist.artist_url %>"><%= playlist.artist %></a></span>
<% } else { %>
<span class="playlist-artist"><%= playlist.artist %></span>
<% } %>
