// assets/article_list.js
import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

$(document).ready(function() {
    const table = $('#articles-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/articles',
            type: 'GET',
            data: function(d) {
                return {
                    draw: d.draw,
                    start: d.start,
                    length: d.length,
                    search: d.search.value,
                    order: d.order
                };
            }
        },
        columns: [
            { data: 'id' },
            { 
                data: 'title',
                render: function(data, type, row) {
                    return `<a href="/article/${row.id}">${data}</a>`;
                }
            },
            { data: 'categories' },
            { data: 'commentsCount' },
            { data: 'likesCount' },
            { data: 'createdAt' },
            { 
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <a href="/article/${row.id}/edit" class="btn btn-sm btn-primary">Modifier</a>
                        <button class="btn btn-sm btn-danger delete-btn"
                                data-article-id="${row.id}"
                                data-article-title="${row.title.replace(/"/g, '&quot;')}"
                                data-csrf-token="${row.csrfToken}">
                            Supprimer
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'desc']]
    });

    $('#search-article').on('keyup', function() {
        table.search(this.value).draw();
    });
});