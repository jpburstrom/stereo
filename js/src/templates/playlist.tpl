<span class="playlist">
<% if (playlist.url !== null && playlist.url.length > 0) { %>
<a href="<%= playlist.url %>"><%= playlist.title %></a>
<% } else { %>
<%= playlist.title %>
<% } %>
</span>
