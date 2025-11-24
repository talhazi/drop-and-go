# Dual Folder System: RightPlace + HappyFile Integration

## Overview

The RightPlace Media Folders feature now supports **both RightPlace folders and HappyFile folders** through a unified API. This allows the desktop application to manage folders from both systems seamlessly.

## Supported Folder Types

### 🗂️ **RightPlace Folders**
- **Taxonomy**: `rightplace_folder`
- **Type Parameter**: `'rightplace'` (default)
- **Features**: Native RightPlace folder system
- **Integration**: Full RightPlace plugin integration

### 📁 **HappyFile Folders**
- **Taxonomy**: `happyfiles_category`
- **Type Parameter**: `'happyfile'` or `'happyfiles'`
- **Features**: HappyFile plugin compatibility
- **Integration**: Works with existing HappyFile installations

## Key Features

### ✅ **Unified API**
- **Single Interface**: All folder operations work with both systems
- **Type Parameter**: Specify folder type via `type` parameter
- **Backward Compatibility**: Existing code continues to work (defaults to RightPlace)

### 🔄 **Cross-System Operations**
- **Create Folders**: Create folders in either system
- **Manage Media**: Add/remove/move media between folders in either system
- **Search & Browse**: Search and browse folders from both systems
- **Hierarchical Structure**: Both systems support parent-child relationships

### 🛡️ **Data Integrity**
- **Separate Taxonomies**: Each system maintains its own taxonomy
- **No Conflicts**: RightPlace and HappyFile folders are completely separate
- **Automatic Cleanup**: WordPress handles taxonomy cleanup automatically

## API Usage

### Folder Management

#### Create Folder
```javascript
// Create RightPlace folder
await createFolder(ctx, {
  hostUrl: 'https://example.com',
  params: { 
    name: 'My Photos', 
    description: 'Personal collection',
    type: 'rightplace'
  }
});

// Create HappyFile folder
await createFolder(ctx, {
  hostUrl: 'https://example.com',
  params: { 
    name: 'My Photos', 
    description: 'Personal collection',
    type: 'happyfile'
  }
});
```

#### Get Folder Tree
```javascript
// Get RightPlace folder tree
const rightplaceTree = await getFolderTree(ctx, {
  hostUrl: 'https://example.com',
  params: { type: 'rightplace' }
});

// Get HappyFile folder tree
const happyfileTree = await getFolderTree(ctx, {
  hostUrl: 'https://example.com',
  params: { type: 'happyfile' }
});
```

#### Search Folders
```javascript
// Search RightPlace folders
const rightplaceResults = await searchFolders(ctx, {
  hostUrl: 'https://example.com',
  params: { 
    search_term: 'vacation',
    type: 'rightplace'
  }
});

// Search HappyFile folders
const happyfileResults = await searchFolders(ctx, {
  hostUrl: 'https://example.com',
  params: { 
    search_term: 'vacation',
    type: 'happyfile'
  }
});
```

### Media-Folder Operations

#### Add Media to Folder
```javascript
// Add to RightPlace folder
await addMediaToFolder(ctx, {
  hostUrl: 'https://example.com',
  params: { 
    media_id: 123,
    folder_id: 456,
    type: 'rightplace'
  }
});

// Add to HappyFile folder
await addMediaToFolder(ctx, {
  hostUrl: 'https://example.com',
  params: { 
    media_id: 123,
    folder_id: 456,
    type: 'happyfile'
  }
});
```

#### Move Media Between Folders
```javascript
// Move within RightPlace folders
await moveMedia(ctx, {
  hostUrl: 'https://example.com',
  params: { 
    media_id: 123,
    from_folder_id: 456,
    to_folder_id: 789,
    type: 'rightplace'
  }
});

// Move within HappyFile folders
await moveMedia(ctx, {
  hostUrl: 'https://example.com',
  params: { 
    media_id: 123,
    from_folder_id: 456,
    to_folder_id: 789,
    type: 'happyfile'
  }
});
```

## WordPress Plugin Implementation

### PHP Backend

#### Taxonomy Registration
```php
// RightPlace folders
register_taxonomy('rightplace_folder', 'attachment', [
    'hierarchical' => true,
    'show_ui' => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'rest_base' => 'rightplace-folders',
]);

// HappyFile folders (already registered by HappyFile plugin)
// Uses 'happyfiles_category' taxonomy
```

#### Dynamic Taxonomy Selection
```php
private function get_folder_taxonomy($type = 'rightplace') {
    switch ($type) {
        case 'happyfile':
        case 'happyfiles':
            return 'happyfiles_category';
        case 'rightplace':
        default:
            return 'rightplace_folder';
    }
}
```

#### API Methods
All folder management methods now accept a `type` parameter:
- `create_folder($params)` - `$params['type']` determines taxonomy
- `update_folder($params)` - `$params['type']` determines taxonomy
- `delete_folder($params)` - `$params['type']` determines taxonomy
- `get_folder_tree($params)` - `$params['type']` determines taxonomy
- `search_folders($params)` - `$params['type']` determines taxonomy
- `add_media_to_folder($params)` - `$params['type']` determines taxonomy
- `remove_media_from_folder($params)` - `$params['type']` determines taxonomy
- `move_media($params)` - `$params['type']` determines taxonomy
- `copy_media_to_folder($params)` - `$params['type']` determines taxonomy
- `get_folders_for_media($params)` - `$params['type']` determines taxonomy

## Desktop Application Integration

### TypeScript Interface

#### Updated JSDoc Comments
All folder management methods now include the `type` parameter in their JSDoc documentation:

```typescript
/**
 * Creates a new media folder
 * @param {string} [options.params.type] - Folder type: 'rightplace' (default) or 'happyfile'
 */
const createFolder = async (ctx: any, { hostUrl, params }: any) => {
    // Implementation
};
```

#### Backward Compatibility
- **Default Behavior**: If no `type` is specified, defaults to `'rightplace'`
- **Existing Code**: All existing code continues to work without changes
- **Optional Parameter**: `type` parameter is optional in all methods

## Benefits

### 🎯 **User Experience**
- **Unified Interface**: Manage both folder systems from one interface
- **Flexibility**: Choose the folder system that works best for each use case
- **Migration Path**: Easy migration between folder systems

### 🔧 **Developer Experience**
- **Single API**: One set of methods for both systems
- **Type Safety**: Clear type parameter for folder system selection
- **Documentation**: Comprehensive JSDoc with examples for both systems

### 🏗️ **Architecture**
- **Separation of Concerns**: Each system maintains its own taxonomy
- **Extensibility**: Easy to add support for additional folder systems
- **Performance**: No performance impact from supporting multiple systems

## Migration Guide

### From Single System to Dual System

#### Existing RightPlace Code
```javascript
// Old code (still works)
await createFolder(ctx, {
  hostUrl: 'https://example.com',
  params: { name: 'My Folder' }
});

// New code (explicit)
await createFolder(ctx, {
  hostUrl: 'https://example.com',
  params: { name: 'My Folder', type: 'rightplace' }
});
```

#### Adding HappyFile Support
```javascript
// New HappyFile support
await createFolder(ctx, {
  hostUrl: 'https://example.com',
  params: { name: 'My Folder', type: 'happyfile' }
});
```

### Best Practices

1. **Explicit Type**: Always specify the `type` parameter for clarity
2. **Consistent Usage**: Use the same type throughout related operations
3. **Error Handling**: Handle cases where a folder system might not be available
4. **User Feedback**: Inform users which folder system they're working with

## Future Enhancements

- **Folder System Detection**: Auto-detect available folder systems
- **Cross-System Operations**: Move media between different folder systems
- **Bulk Operations**: Bulk operations across multiple folder systems
- **Folder System Preferences**: User preferences for default folder system
- **Folder System Migration**: Tools to migrate between folder systems 