class Notes {
  constructor() {
    this.editButton = document.querySelector(".edit-note");
    this.deleteButton = document.querySelector(".delete-note");
    this.events()
  }
    
    events() {
        this.deleteButton.addEventListener('click', this.deleteNote)
    }

    deleteNote = () => {
        fetch(`${universityData.url}/wp-json/wp/v2/note/79`, {
            method: "DELETE",
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': universityData.nonce
            }
        }).then(res => res.json()).then(data => {
            console.log(data)
        }).catch(error => {
            console.log(error)
        })
    }
}

export default Notes;
