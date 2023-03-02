import $ from "jquery";

class Search {
  constructor() {
    this.addSearchHTML();
    this.openButton = $(".js-search-trigger");
    this.closeButton = $(".search-overlay__close");
    this.searchOverlay = $(".search-overlay");
    this.searchField = $("#search-term");
    this.resultsDiv = $("#search-overlay__results");
    this.typingTimer;
    this.previousValue;
    this.isOverlayOpened = false;
    this.isSpinnerVisible = false;
    this.events();
  }

  events() {
    this.openButton.on("click", this.openOverlay.bind(this));
    this.closeButton.on("click", this.closeOverlay.bind(this));
    $(document).on("keydown", this.keyPressDispatcher.bind(this));
    this.searchField.on("keyup", this.typingLogic.bind(this));
  }

  getResults() {
    $.getJSON(
      `${
        universityData.root_url
      }/wp-json/university/v1/search?term=${this.searchField.val()}`,
      (results) => {
        this.resultsDiv.html(`
        <div class="row">
          <div class="one-third">
            <h2 class="search-overlay__section-title">General Information</h2>
            ${
              results.generalInfo.length > 0
                ? `<ul class="link-list min-list">
                    ${results.generalInfo
                      .map(
                        (item) =>
                          `<li><a href="${item.permalink}">${item.title}</a>${
                            item.postType == "post"
                              ? ` By ${item.authorName}`
                              : ""
                          }</li>`
                      )
                      .join("")}
                </ul>`
                : `<p>No general info matches your search term.</p>`
            }
          </div>
          <div class="one-third">
            <h2 class="search-overlay__section-title">Programs</h2>
                        ${
                          results.programs.length > 0
                            ? `<ul class="link-list min-list">
                    ${results.programs
                      .map(
                        (item) =>
                          `<li><a href="${item.permalink}">${item.title}</a></li>`
                      )
                      .join("")}
                </ul>`
                            : `<p>No programs matches your search term.</p>`
                        }
            <h2 class="search-overlay__section-title">Professors</h2>
            ${
              results.professors.length > 0
                ? `<ul class="professor-cards">
                    ${results.professors
                      .map(
                        (item) =>
                          `
                        <li class="professor-card__list-item">
                          <a class="professor-card" href="${item.permalink}">
                            <img class="professor-card__image" src="${item.imageUrl}" />
                            <span class="professor-card__name">${item.title}</span>
                          </a>
                        </li>
                          `
                      )
                      .join("")}
                </ul>`
                : `<p>No professors matches your search term.</p>`
            }
          </div>
          <div class="one-third">
            <h2 class="search-overlay__section-title">Campuses</h2>
            ${
              results.campuses.length > 0
                ? `<ul class="link-list min-list">
                    ${results.campuses
                      .map(
                        (item) =>
                          `<li><a href="${item.permalink}">${item.title}</a></li>`
                      )
                      .join("")}
                </ul>`
                : `<p>No campuses matches your search term.</p>`
            }

            <h2 class="search-overlay__section-title">Events</h2>
            ${
              results.events.length > 0
                ? `${results.events
                    .map(
                      (item) =>
                        `
                        <div class="event-summary">
                        <a class="event-summary__date t-center" href="${item.permalink}">
                          <span class="event-summary__month">
                           ${item.month}
                          </span>
                          <span class="event-summary__day">${item.day}</span>
                        </a>
                        <div class="event-summary__content">
                          <h5 class="event-summary__title headline headline--tiny"><a href="${item.permalink}">${item.title}</a></h5>
                          <p>${item.description} <a href="${item.permalink}" class="nu gray">Learn more</a></p>
                        </div>
                      </div>
                          `
                    )
                    .join("")}`
                : `<p>No events matches your search term.</p>`
            }
          </div>
        </div>
      `);
        this.isSpinnerVisible = false;
      }
    );
  }

  typingLogic() {
    if (this.searchField.val() !== this.previousValue) {
      clearTimeout(this.typingTimer);

      if (this.searchField.val()) {
        if (!this.isSpinnerVisible) {
          this.resultsDiv.html('<div class="spinner-loader"></div>');
          this.isSpinnerVisible = true;
        }
        this.typingTimer = setTimeout(this.getResults.bind(this), 750);
      } else {
        this.resultsDiv.html("");
        this.isSpinnerVisible = false;
      }
    }

    this.previousValue = this.searchField.val();
  }

  keyPressDispatcher(e) {
    if (
      e.keyCode === 83 &&
      !this.isOverlayOpened &&
      !$("input, textarea").is(":focus")
    ) {
      this.openOverlay();
    }

    if (e.keyCode === 27 && this.isOverlayOpened) {
      this.closeOverlay();
    }
  }

  openOverlay() {
    this.searchOverlay.addClass("search-overlay--active");
    $("body").addClass("body-no-scroll");
    this.searchField.val("");
    this.resultsDiv.html("")
    setTimeout(() => this.searchField.trigger("focus"), 301);
    this.isOverlayOpened = true;
    return false
  }

  closeOverlay() {
    this.searchOverlay.removeClass("search-overlay--active");
    $("body").removeClass("body-no-scroll");
    this.isOverlayOpened = false;
  }

  addSearchHTML() {
    $("body").append(`
<div class="search-overlay">
    <div class="search-overlay__top">
      <div class="container">
        <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
        <input type="text" class="search-term" placeholder="What are you looking for?" id="search-term">
        <i class="fa fa-window-close search-overlay__close" aria-hidden="true"></i>
      </div>
    </div>
    <div class="container">
      <div id="search-overlay__results"></div>
    </div>
  </div>
        `);
  }
}

export default Search;
