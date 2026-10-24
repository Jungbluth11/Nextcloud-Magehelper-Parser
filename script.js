const tabs = Array.from(document.querySelectorAll("#xmlviewer [role='tab']"))
const viewer = document.getElementById("xmlviewer")

// Nextcloud uses different layout containers for user and public pages.
// Place the viewer at the document root so both modes share the same layout.
if (viewer) {
	document.body.appendChild(viewer)
	document.documentElement.style.setProperty("overflow", "hidden", "important")
	document.body.style.setProperty("overflow", "hidden", "important")
	const viewerRect = viewer.getBoundingClientRect()
	viewer.style.position = "fixed"
	viewer.style.top = `${viewerRect.top}px`
	viewer.style.left = `${viewerRect.left}px`
	viewer.style.right = `${Math.max(0, window.innerWidth - viewerRect.right)}px`
	viewer.style.zIndex = "1"
	for (const container of document.querySelectorAll("#content, #app-content, #content-vue, #body-user, #body-public, .app-content")) {
		container.style.setProperty("overflow", "hidden", "important")
	}
}

function resizePanels() {
	if (!viewer) return
	const panel = viewer.querySelector(".xmlviewer-card:not([hidden])")
	if (!panel) return
	const availableHeight = Math.max(160, window.innerHeight - panel.getBoundingClientRect().top - 16)
	viewer.style.setProperty("--xmlviewer-panel-height", `${availableHeight}px`)
}

function activateTab(tab) {
	for (const candidate of tabs) {
		const active = candidate === tab
		candidate.setAttribute("aria-selected", String(active))
		candidate.tabIndex = active ? 0 : -1
		const panel = document.getElementById(candidate.getAttribute("aria-controls"))
		panel.hidden = !active
		panel.style.setProperty("display", active ? "block" : "none", "important")
	}
	resizePanels()
}

if (tabs.length > 0) activateTab(tabs.find((tab) => tab.getAttribute("aria-selected") === "true") || tabs[0])
window.addEventListener("resize", resizePanels)

for (const tab of tabs) {
	tab.addEventListener("click", () => activateTab(tab))
	tab.addEventListener("keydown", (event) => {
		if (!["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) return
		event.preventDefault()
		const current = tabs.indexOf(tab)
		const next = event.key === "Home" ? 0 : event.key === "End" ? tabs.length - 1
			: (current + (event.key === "ArrowRight" ? 1 : -1) + tabs.length) % tabs.length
		tabs[next].focus()
		activateTab(tabs[next])
	})
}

