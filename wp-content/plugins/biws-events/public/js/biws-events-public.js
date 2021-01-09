class BIWSSearchResult {
    data = {
        action: 'biws_event_search',
    };

    eventIDPrefix = "biws-event-id-";

    constructor(root) {
        this.root = root;
    }

    svgBase = d => {
        var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xlink", "http://www.w3.org/1999/xlink");
        svg.setAttribute('viewbox', '0 0 24 24');
        svg.setAttribute('width', '24px');
        svg.setAttribute('height', '24px');

        var path = document.createElementNS("http://www.w3.org/2000/svg", 'path');
        path.setAttribute('d', d);
        path.setAttribute('fill', '#56704c');

        svg.appendChild(path);

        return svg;
    }

    svgDate = () => {
        return this.svgBase('M9,10H7V12H9V10M13,10H11V12H13V10M17,10H15V12H17V10M19,3H18V1H16V3H8V1H6V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M19,19H5V8H19V19Z');
    }

    svgVenue = () => {
        return this.svgBase('M12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5M12,2A7,7 0 0,1 19,9C19,14.25 12,22 12,22C12,22 5,14.25 5,9A7,7 0 0,1 12,2M12,4A5,5 0 0,0 7,9C7,10 7,12 12,18.71C17,12 17,10 17,9A5,5 0 0,0 12,4Z');
    }

    svgDisciplines = () => {
        return this.svgBase('M2,2V4H7V8H2V10H7C8.11,10 9,9.11 9,8V7H14V17H9V16C9,14.89 8.11,14 7,14H2V16H7V20H2V22H7C8.11,22 9,21.11 9,20V19H14C15.11,19 16,18.11 16,17V13H22V11H16V7C16,5.89 15.11,5 14,5H9V4C9,2.89 8.11,2 7,2H2Z');
    }

    buildEventBanner = entry => {
        var day = document.createElement('div');
        day.classList.add('biws-event-entry-date-day');
        day.innerHTML = entry.start.day;

        var month = document.createElement('div');
        month.classList.add('biws-event-entry-date-month');
        month.innerHTML = entry.start.month;

        var date = document.createElement('div');
        date.classList.add('biws-event-entry-date');
        date.appendChild(day);
        date.appendChild(month);

        var banner = document.createElement('div');
        banner.classList.add('biws-event-entry-banner');
        banner.appendChild(date);
        if (entry.thumbnail && entry.highlight) {
            banner.setAttribute('style', `background:#1a1a1a url('${entry.thumbnail}')!important; background-size:cover!important;`);
        }

        return banner;
    }

    /*
    div.info
        h3.title
        p.category
        div.date
            img.icon
            p.date (from to)
        div.place
            img.icon
            p.content
        div.disciplines (if length != 0)
            img.icon
            ul.content
                li.discipline
        div.tags (if length != 0)
            div.tags
    */
    buildEventInfo = entry => {
        var title, category, date, venue, disciplines, tags;

        title = document.createElement('h3');
        title.classList.add('biws-event-entry-title');
        title.innerHTML = entry.title;

        if (entry.category) {
            category = document.createElement('p');
            category.classList.add('biws-event-entry-category');
            category.classList.add('has-large-font-size');
            category.innerHTML = entry.category;
        }

        var dateIcon = this.svgDate();
        dateIcon.classList.add('biws-event-entry-info-icon');

        var dateContent = document.createElement('p');
        if (entry.start.day !== entry.end.day || entry.start.month !== entry.end.month) {
            dateContent.innerHTML = `${entry.start.day}. ${entry.start.month} - ${entry.end.day}. ${entry.end.month}`;
        } else {
            dateContent.innerHTML = `${entry.start.day}. ${entry.start.month}`;
        }

        date = document.createElement('div');
        date.classList.add("biws-event-entry-date");
        date.classList.add('has-small-font-size');
        date.classList.add('biws-event-entry-detail');
        date.appendChild(dateIcon);
        date.appendChild(dateContent);

        if (entry.venue) {
            var venueIcon = this.svgVenue();
            venueIcon.classList.add('biws-event-entry-info-icon');

            var venueString = `<b style="text-transform:uppercase">${entry.venue.name}</b>`;
            if (entry.venue.street || entry.venue.zip || entry.venue.location) {
                venueString += ', ';
                if (entry.venue.street) {
                    venueString += entry.venue.street;
                    if (entry.venue.zip || entry.venue.location) {
                        venueString += ", ";
                    }
                }
                if (entry.venue.zip) {
                    venueString += entry.venue.zip;
                    if (entry.venue.location) {
                        venueString += " ";
                    }
                }
                if (entry.venue.location) {
                    venueString += entry.venue.location;
                }
            }

            var venueContent = document.createElement('p');
            venueContent.innerHTML = venueString;

            venue = document.createElement('div');
            venue.classList.add("biws-event-entry-venue");
            venue.classList.add("has-small-font-size");
            venue.classList.add('biws-event-entry-detail');
            venue.appendChild(venueIcon);
            venue.appendChild(venueContent);
        }

        if (entry.disciplines) {
            var disciplinesIcon = this.svgDisciplines();
            disciplinesIcon.classList.add('biws-event-entry-info-icon');

            var disciplinesContent = document.createElement('p');
            disciplinesContent.innerHTML = entry.disciplines.join(', ');

            disciplines = document.createElement('div');
            disciplines.classList.add("biws-event-entry-disciplines");
            disciplines.classList.add("has-small-font-size");
            disciplines.classList.add('biws-event-entry-detail');
            disciplines.appendChild(disciplinesIcon);
            disciplines.appendChild(disciplinesContent);
        }

        if (entry.tags) {
            var tag = document.createElement('p');
            disciplinesContent.innerHTML = entry.disciplines.join(', ');

            tags = document.createElement('div');
            tags.classList.add("biws-event-entry-tags");
            tags.classList.add("has-small-font-size");
            entry.tags.forEach(term => {
                tag = document.createElement('p');
                tag.classList.add('biws-event-entry-tag');
                if (term.color) {
                    tag.setAttribute('style', `background:#${term.color}!important`);
                }
                tag.innerHTML = term.name;
                tags.appendChild(tag);
            })
        }

        var infoDiv = document.createElement('div');
        infoDiv.classList.add("biws-event-entry-info");
        infoDiv.appendChild(title);
        if (category) {
            infoDiv.appendChild(category);
        }
        infoDiv.appendChild(date);
        if (venue) {
            infoDiv.appendChild(venue);
        }
        if (disciplines) {
            infoDiv.appendChild(disciplines);
        }
        if (tags) {
            infoDiv.appendChild(tags);
        }

        return infoDiv;
    }

    /*
    div.wrapper
        a.permalink
            div.entry
                div.banner
                div.info
    */
    buildFragment = entry => {
        var entryDiv = document.createElement('div');
        entryDiv.classList.add('biws-event-entry-inner');
        entryDiv.appendChild(this.buildEventBanner(entry));
        entryDiv.appendChild(this.buildEventInfo(entry));

        var a = document.createElement('a');
        a.classList.add('biws-event-entry-link');
        a.href = entry.permalink;
        a.appendChild(entryDiv);

        var wrapper = document.createElement('div');
        wrapper.classList.add('biws-event-entry-wrapper');
        if (entry.highlight) {
            wrapper.classList.add('biws-event-entry-highlight');
        }
        wrapper.id = this.eventIDPrefix + entry.id;
        wrapper.appendChild(a);

        var fragment = document.createDocumentFragment();
        fragment.appendChild(wrapper);

        return fragment;
    }

    appendSearchResultEntry = entry => {
        this.root.appendChild(this.buildFragment(entry));
    }

    appendNoResultsEntry = () => {
        this.root.innerHTML = "<i><small>Es wurden noch keine Veranstaltungen angekündigt.</small></i>";
    }

    search = data => {
        const request = new XMLHttpRequest();

        request.onreadystatechange = () => {
            if (request.readyState == XMLHttpRequest.DONE) {
                if (request.status == 200) {
                    let results = JSON.parse(request.responseText)
                    if (results.length) {
                        results.forEach(this.appendSearchResultEntry);
                    } else {
                        this.appendNoResultsEntry();
                    }
                }
            }
        };

        request.open('POST', ajax_url, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
        var params = new URLSearchParams(data);
        request.send(params.toString());
    }

    init = () => this.search(this.data);
}

new BIWSSearchResult(document.getElementById('biws-events')).init();


