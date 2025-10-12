import { onMounted } from 'vue'

export function useMetronic() {
  onMounted(() => {
    // Initialize KTMenu component
    const initMenu = () => {
      const menuElements = document.querySelectorAll('[data-kt-menu="true"]')
      
      menuElements.forEach((element) => {
        // Handle accordion menu items
        const accordionItems = element.querySelectorAll('.menu-accordion')
        
        accordionItems.forEach((item) => {
          const link = item.querySelector('.menu-link')
          const subMenu = item.querySelector('.menu-sub')
          
          if (link && subMenu) {
            link.addEventListener('click', (e) => {
              e.preventDefault()
              
              // Toggle current item
              const isActive = item.classList.contains('hover')
              const isShown = item.classList.contains('show')
              
              // Close other open items in the same level
              const parent = item.parentElement
              if (parent) {
                const siblings = parent.querySelectorAll('.menu-accordion')
                siblings.forEach((sibling) => {
                  if (sibling !== item) {
                    sibling.classList.remove('hover', 'show')
                    const siblingSubMenu = sibling.querySelector('.menu-sub')
                    if (siblingSubMenu) {
                      siblingSubMenu.style.display = 'none'
                    }
                  }
                })
              }
              
              // Toggle current item
              if (isActive || isShown) {
                item.classList.remove('hover', 'show')
                subMenu.style.display = 'none'
              } else {
                item.classList.add('hover', 'show')
                subMenu.style.display = 'block'
              }
            })
          }
        })
        
        // Handle active menu item
        const activeLink = element.querySelector('.menu-link.active')
        if (activeLink) {
          let parent = activeLink.closest('.menu-accordion')
          while (parent) {
            parent.classList.add('hover', 'show')
            const subMenu = parent.querySelector('.menu-sub')
            if (subMenu) {
              subMenu.style.display = 'block'
            }
            parent = parent.parentElement?.closest('.menu-accordion') || null
          }
        }
      })
    }

    // Initialize scroll
    const initScroll = () => {
      const scrollElements = document.querySelectorAll('[data-kt-scroll="true"]')
      
      scrollElements.forEach((element) => {
        if (element instanceof HTMLElement) {
          element.style.overflowY = 'auto'
          element.style.maxHeight = '100%'
        }
      })
    }

    // Run initializations
    setTimeout(() => {
      initMenu()
      initScroll()
    }, 100)
  })
}
