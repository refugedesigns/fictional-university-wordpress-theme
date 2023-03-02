import axios from "axios";

class Like {
  constructor() {
    this.likeBox = document.querySelector(".like-box");
    if (this.likeBox) {
      axios.defaults.headers.common["X-WP-Nonce"] = universityData.nonce;
      this.events();
    }
  }

  events() {
    this.likeBox.addEventListener("click", (e) => this.clickDispatcher(e));
  }

  //methods
  clickDispatcher(e) {
    let currentLikeBox = e.target;
    while (!currentLikeBox.classList.contains("like-box")) {
      currentLikeBox = currentLikeBox.parentElement;
    }
    if (currentLikeBox.getAttribute("data-exists") == "yes") {
      this.deleteLike(currentLikeBox);
    } else {
      this.createLike(currentLikeBox);
    }
  }

  async createLike(currentLikeBox) {
    try {
      const response = await axios.post(
        `${universityData.root_url}/wp-json/university/v1/manageLike`,
        { professorId: currentLikeBox.getAttribute("data-professor") }
      );
      if (response.data !== "Only logged in users can create a like.") {
        currentLikeBox.setAttribute("data-exists", "yes");
        let likeCount = parseInt(
          currentLikeBox.querySelector(".like-count").innerHTML,
          10
        );
        likeCount++;
        currentLikeBox.querySelector(".like-count").innerHTML = likeCount;
        currentLikeBox.setAttribute("data-like", response.data);
      }
      console.log(response.data);
    } catch (error) {
      console.log(error);
    }
  }

  async deleteLike(currentLikeBox) {
    try {
      const response = await axios({
        url: `${universityData.root_url}/wp-json/university/v1/manageLike`,
        method: "delete",
        data: { like: currentLikeBox.getAttribute("data-like") },
      });
      currentLikeBox.setAttribute("data-exists", "no");
      let likeCount = parseInt(
        currentLikeBox.querySelector(".like-count").innerHTML,
        10
      );
      likeCount--;
      currentLikeBox.querySelector(".like-count").innerHTML = likeCount;
      currentLikeBox.setAttribute("data-like", "");
      console.log(response.data);
    } catch (error) {
      console.log(response.data);
    }
  }
}

export default Like;
