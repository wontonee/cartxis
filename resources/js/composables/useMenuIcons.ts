import { Component } from 'vue'
import {
  LayoutDashboard,
  ShoppingBag,
  ShoppingCart,
  Users,
  TrendingUp,
  FileText,
  BarChart3,
  Settings,
  Package,
  FolderTree,
  ListChecks,
  Tag,
  Star,
  Receipt,
  Truck,
  CreditCard,
  Megaphone,
  Ticket,
  Mail,
  Globe,
  BookOpen,
  Newspaper,
  Image,
  Server,
  Shield,
  Wrench,
  Store,
  Flag,
  Smartphone,
  Percent,
} from 'lucide-vue-next'

export interface IconMap {
  [key: string]: Component
}

export function useMenuIcons() {
  const iconMap: IconMap = {
    'layout-dashboard': LayoutDashboard,
    'shopping-bag': ShoppingBag,
    'shopping-cart': ShoppingCart,
    'users': Users,
    'trending-up': TrendingUp,
    'file-text': FileText,
    'bar-chart-3': BarChart3,
    'settings': Settings,
    'package': Package,
    'folder-tree': FolderTree,
    'list-checks': ListChecks,
    'tag': Tag,
    'star': Star,
    'receipt': Receipt,
    'truck': Truck,
    'credit-card': CreditCard,
    'megaphone': Megaphone,
    'ticket': Ticket,
    'mail': Mail,
    'globe': Globe,
    'book-open': BookOpen,
    'newspaper': Newspaper,
    'image': Image,
    'server': Server,
    'shield': Shield,
    'wrench': Wrench,
    'shop': Store,
    'store': Store,
    'flag': Flag,
    'devices': Smartphone,
    'percent': Percent,
  }

  const getIcon = (iconName: string): Component | null => {
    return iconMap[iconName] || null
  }

  return {
    iconMap,
    getIcon,
  }
}
