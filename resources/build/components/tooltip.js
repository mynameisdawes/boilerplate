let _tooltip = {
	name: "c-tooltip",
	template: `
		<div class="tooltip" @click.stop>
			<span class="tooltip-icon"></span>
			<div class="popup">
				<slot></slot>
			</div>
		</div>
	`
}

export default _tooltip;