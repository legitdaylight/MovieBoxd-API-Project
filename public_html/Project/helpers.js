function flash(message = "", color = "info") {
    let flash = document.getElementById("flash");
    //create a div (or whatever wrapper we want)
    let outerDiv = document.createElement("div");
    outerDiv.className = "row justify-content-center";
    let innerDiv = document.createElement("div");

    //apply the CSS (these are bootstrap classes which we'll learn later)
    innerDiv.className = `alert alert-${color}`;
    //set the content
    innerDiv.innerText = message;

    outerDiv.appendChild(innerDiv);
    //add the element to the DOM (if we don't it merely exists in memory)
    flash.appendChild(outerDiv);
}
function validate_email(email)
{
    console.log("Inside validate email");
    emailRegEx = /^([a-z0-9_\.\+-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/;
    return (emailRegEx.test(email));
}
function validate_username(username)
{
    usernameRegEx = /^[a-z0-9_-]{3,16}$/;
    return (usernameRegEx.test(username));
}